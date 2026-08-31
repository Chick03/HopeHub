<?php
session_start();
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth_check.php';
require_once __DIR__ . '/razorpay_helper.php';
require_donor();

$donationId = (int)($_GET['donation_id'] ?? 0);
$donor = current_user();

$stmt = $pdo->prepare("SELECT * FROM donations WHERE donation_id = ? AND donor_id = ?");
$stmt->execute([$donationId, $donor['user_id']]);
$donation = $stmt->fetch();

if (!$donation) { die('Donation not found.'); }
if ($donation['status'] !== 'pending' && $donation['status'] !== 'failed') {
    redirect('/hopehub/donor/donation_history.php');
}

$pageTitle = 'Checkout';
$configIsPlaceholder = (RAZORPAY_KEY_ID === 'rzp_test_YOUR_KEY_ID_HERE');
$orderResult = null;

if (!$configIsPlaceholder) {
    $orderResult = razorpayCreateOrder((float)$donation['amount'], 'donation_' . $donationId);
    if ($orderResult['success']) {
        $pdo->prepare("UPDATE donations SET razorpay_order_id = ? WHERE donation_id = ?")
            ->execute([$orderResult['order']['id'], $donationId]);
    }
}

include __DIR__ . '/../includes/header.php';
?>

<div class="form-card" style="text-align:center;">
    <h2>Checkout</h2>
    <p class="help-text">Completing your donation</p>
    <p style="font-family:'Fraunces',serif; font-size:28px; color:var(--color-forest-dark); margin:12px 0 24px;">
        <?= formatCurrency($donation['amount']) ?>
    </p>

    <?php if ($configIsPlaceholder): ?>
        <div class="alert alert-error" style="text-align:left;">
            Payment gateway isn't configured yet. An admin needs to add real
            Razorpay test keys to <code>config/razorpay_config.php</code>
            before donations can be processed &mdash; see the README for the
            2-minute signup steps.
        </div>
    <?php elseif (!$orderResult['success']): ?>
        <div class="alert alert-error" style="text-align:left;">
            Couldn't start the payment: <?= clean($orderResult['error']) ?>
        </div>
    <?php else: ?>
        <p class="help-text">You'll choose UPI, Card, Netbanking, or Wallet inside the secure payment window.</p>
        <button id="rzp-pay-button" class="btn btn-primary btn-block">Pay <?= formatCurrency($donation['amount']) ?></button>
        <p class="help-text" style="margin-top:16px;">
            In test mode, use Razorpay's published test card/UPI details to complete this &mdash;
            see <a href="https://razorpay.com/docs/payments/payments/test-card-upi-details/" target="_blank" rel="noopener">their test credentials page</a>.
        </p>

        <script src="https://checkout.razorpay.com/v1/checkout.js"></script>
        <script>
        document.getElementById('rzp-pay-button').addEventListener('click', function () {
            var options = {
                key: <?= json_encode(RAZORPAY_KEY_ID) ?>,
                amount: <?= (int)round($donation['amount'] * 100) ?>,
                currency: 'INR',
                name: <?= json_encode($pdo->query("SELECT name FROM orphanage_profile WHERE id = 1")->fetchColumn()) ?>,
                description: 'Donation',
                order_id: <?= json_encode($orderResult['order']['id']) ?>,
                prefill: {
                    name: <?= json_encode($donor['name']) ?>,
                    email: <?= json_encode($donor['email']) ?>
                },
                theme: { color: '#1B4332' },
                handler: function (response) {
                    document.getElementById('rzp-pay-button').textContent = 'Verifying payment…';
                    document.getElementById('rzp-pay-button').disabled = true;
                    fetch('/hopehub/payment/verify_payment.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({
                            donation_id: <?= (int)$donationId ?>,
                            razorpay_payment_id: response.razorpay_payment_id,
                            razorpay_order_id: response.razorpay_order_id,
                            razorpay_signature: response.razorpay_signature
                        })
                    })
                    .then(function (r) { return r.json(); })
                    .then(function (data) {
                        if (data.success) {
                            window.location.href = '/hopehub/donor/donation_history.php';
                        } else {
                            alert('Payment could not be verified: ' + data.error);
                            window.location.reload();
                        }
                    })
                    .catch(function () {
                        alert('Network error while verifying payment. If money was deducted, contact support with your payment ID.');
                    });
                },
                modal: {
                    ondismiss: function () {
                        // Donor closed the widget without paying — donation stays 'pending', they can retry.
                    }
                }
            };
            var rzp = new Razorpay(options);
            rzp.on('payment.failed', function (response) {
                alert('Payment failed: ' + response.error.description);
            });
            rzp.open();
        });
        </script>
    <?php endif; ?>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
