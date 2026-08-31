<?php
session_start();
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth_check.php';
require_donor();

$org = getOrphanageProfile($pdo);
$needs = $pdo->query("SELECT * FROM needs WHERE status = 'open'")->fetchAll();

$preselectNeed = (int)($_GET['need_id'] ?? 0);

$pageTitle = 'Donate';
include __DIR__ . '/../includes/header.php';
?>

<div class="form-card">
    <h2>Donate to <?= clean($org['name']) ?></h2>
    <form action="/hopehub/donor/process_donation.php" method="post" class="validate">

        <div class="form-group">
            <label for="donation_type">Donation Type</label>
            <select name="donation_type" id="donation_type" required>
                <option value="Cash">Cash</option>
                <option value="Food">Food</option>
                <option value="Clothes">Clothes</option>
                <option value="Books">Books</option>
                <option value="Medical">Medical Supplies</option>
            </select>
        </div>

        <?php if (!empty($needs)): ?>
        <div class="form-group">
            <label for="need_id">Link to a specific need (optional)</label>
            <select name="need_id" id="need_id">
                <option value="">— General donation —</option>
                <?php foreach ($needs as $n): ?>
                <option value="<?= (int)$n['need_id'] ?>" <?= $preselectNeed === (int)$n['need_id'] ? 'selected' : '' ?>>
                    <?= (int)$n['quantity'] ?> &times; <?= clean($n['item_name']) ?> (<?= clean($n['category']) ?>)
                </option>
                <?php endforeach; ?>
            </select>
        </div>
        <?php endif; ?>

        <div class="form-group" id="amount-group">
            <label for="amount">Amount (₹)</label>
            <input type="number" name="amount" id="amount" min="1" step="0.01" placeholder="e.g. 1500" required>
        </div>

        <div class="form-group" id="quantity-group" style="display:none;">
            <label for="quantity">What are you sending?</label>
            <input type="text" name="quantity" id="quantity" placeholder="e.g. 20 blankets, 15 storybooks">
        </div>

        <div class="card" id="shipping-info" style="display:none; margin-bottom:20px;">
            <div class="card-body">
                <h3 style="margin-bottom:8px;">Send your parcel to</h3>
                <p style="margin:0 0 4px;"><strong><?= clean($org['name']) ?></strong></p>
                <p class="card-meta" style="margin:0 0 10px;"><?= clean($org['address']) ?></p>
                <?php if (!empty($org['mobile_number'])): ?>
                <p class="card-meta" style="margin:0;">Call ahead: <?= clean($org['mobile_number']) ?></p>
                <?php endif; ?>
                <p class="help-text" style="margin-top:10px;">Ship or courier the item(s) to this address, then submit this form so we know to expect your parcel. If you have a courier tracking number, add it in Notes below — it helps us confirm receipt faster.</p>
            </div>
        </div>

        <div class="form-group">
            <label for="notes">Notes (optional)</label>
            <textarea name="notes" id="notes" placeholder="Courier tracking number, expected delivery date, or anything else we should know"></textarea>
        </div>

        <button type="submit" class="btn btn-primary btn-block">Continue</button>
    </form>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
