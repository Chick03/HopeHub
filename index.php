<?php
session_start();
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/auth_check.php';

$pageTitle = 'Home';
$org = getOrphanageProfile($pdo);

$stats = $pdo->query(
    "SELECT
        (SELECT COUNT(*) FROM donations WHERE status IN ('success','verified')) AS total_donations,
        (SELECT COUNT(*) FROM orphans) AS total_children"
)->fetch();

$whatsappDigits = preg_replace('/\D/', '', (string)$org['whatsapp_number']);
$whatsappLink = strlen($whatsappDigits) >= 10 ? 'https://wa.me/91' . $whatsappDigits : null;

include __DIR__ . '/includes/header.php';
?>

<section class="hero">
    <h1><?= clean($org['name']) ?></h1>
    <p><?= clean(mb_strimwidth($org['description'], 0, 220, '...')) ?></p>
    <div class="hero-actions">
        <a href="/hopehub/donor/donate.php" class="btn btn-primary">Donate Now</a>
        <a href="#leaderboard" class="btn btn-outline">See Our Top Donors</a>
    </div>
    <p class="help-text" style="margin-top:16px;"><?= clean($org['address']) ?></p>
</section>

<section class="stats-row">
    <div class="stat-card">
        <div class="stat-value"><?= (int)$stats['total_children'] ?></div>
        <div class="stat-label">Children in Our Care</div>
    </div>
    <div class="stat-card">
        <div class="stat-value"><?= (int)$stats['total_donations'] ?></div>
        <div class="stat-label">Donations Received</div>
    </div>
</section>

<div class="two-col">
    <div>
        <h2>About Us</h2>
        <p><?= nl2br(clean($org['description'])) ?></p>

        <?php if (!empty($org['founder_name'])): ?>
        <h3 style="margin-top:28px;">Our Founder</h3>
        <p><strong><?= clean($org['founder_name']) ?></strong></p>
        <?php if (!empty($org['founder_bio'])): ?>
        <p><?= nl2br(clean($org['founder_bio'])) ?></p>
        <?php endif; ?>
        <?php endif; ?>
    </div>

    <div>
        <div class="card">
            <div class="card-body">
                <h3>Contact & Trust Info</h3>
                <?php if (!empty($org['registration_number'])): ?>
                    <p class="card-meta">Registration No.<br><strong><?= clean($org['registration_number']) ?></strong></p>
                <?php endif; ?>
                <?php if (!empty($org['email'])): ?>
                    <p class="card-meta">Email<br><a href="mailto:<?= clean($org['email']) ?>"><?= clean($org['email']) ?></a></p>
                <?php endif; ?>
                <?php if (!empty($org['mobile_number'])): ?>
                    <p class="card-meta">Phone<br><a href="tel:<?= clean($org['mobile_number']) ?>"><?= clean($org['mobile_number']) ?></a></p>
                <?php endif; ?>
                <?php if ($whatsappLink): ?>
                    <a href="<?= clean($whatsappLink) ?>" target="_blank" rel="noopener" class="btn btn-secondary btn-block" style="margin-top:12px;">Chat on WhatsApp</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<section id="leaderboard" style="margin-top:48px;">
    <div class="section-title" style="margin-top:0;">
        <h2>Top Donors</h2>
    </div>
    <p class="help-text">Ranked by total confirmed cash contributions &mdash; updated live.</p>
    <div id="leaderboard-list" class="card">
        <div class="card-body">
            <p class="help-text" id="leaderboard-status">Loading leaderboard…</p>
        </div>
    </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>
