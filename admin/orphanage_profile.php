<?php
session_start();
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth_check.php';
require_admin();

$org = getOrphanageProfile($pdo);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name        = trim($_POST['name'] ?? '');
    $address     = trim($_POST['address'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $founderName = trim($_POST['founder_name'] ?? '');
    $founderBio  = trim($_POST['founder_bio'] ?? '');
    $regNumber   = trim($_POST['registration_number'] ?? '');
    $email       = trim($_POST['email'] ?? '');
    $mobile      = trim($_POST['mobile_number'] ?? '');
    $whatsapp    = trim($_POST['whatsapp_number'] ?? '');

    if ($name === '' || $address === '') {
        flash('error', 'Name and address are required.');
    } else {
        $stmt = $pdo->prepare(
            "UPDATE orphanage_profile SET
                name=?, address=?, description=?, founder_name=?, founder_bio=?,
                registration_number=?, email=?, mobile_number=?, whatsapp_number=?
             WHERE id = 1"
        );
        $stmt->execute([$name, $address, $description, $founderName, $founderBio, $regNumber, $email, $mobile, $whatsapp]);
        flash('success', 'Orphanage profile updated.');
        redirect('/hopehub/admin/orphanage_profile.php');
    }
    $org = array_merge($org, [
        'name' => $name, 'address' => $address, 'description' => $description,
        'founder_name' => $founderName, 'founder_bio' => $founderBio,
        'registration_number' => $regNumber, 'email' => $email,
        'mobile_number' => $mobile, 'whatsapp_number' => $whatsapp,
    ]);
}

$pageTitle = 'Orphanage Profile';
include __DIR__ . '/../includes/header.php';
?>

<div class="form-card" style="max-width:600px;">
    <h2>Orphanage Profile</h2>
    <p class="help-text">This is the one organization HopeHub represents. Everything here is shown on the public homepage to help donors trust and verify who they're giving to &mdash; keep it accurate and up to date.</p>
    <form method="post" class="validate">
        <div class="form-group">
            <label for="name">Orphanage Name</label>
            <input type="text" name="name" id="name" value="<?= clean($org['name']) ?>" required>
        </div>
        <div class="form-group">
            <label for="address">Address</label>
            <input type="text" name="address" id="address" value="<?= clean($org['address']) ?>" required>
        </div>
        <div class="form-group">
            <label for="description">About / Description</label>
            <textarea name="description" id="description" style="min-height:120px;"><?= clean($org['description']) ?></textarea>
        </div>

        <div class="form-group">
            <label for="founder_name">Founder Name</label>
            <input type="text" name="founder_name" id="founder_name" value="<?= clean($org['founder_name'] ?? '') ?>">
        </div>
        <div class="form-group">
            <label for="founder_bio">Founder Bio</label>
            <textarea name="founder_bio" id="founder_bio"><?= clean($org['founder_bio'] ?? '') ?></textarea>
        </div>
        <div class="form-group">
            <label for="registration_number">Trust / Society Registration Number</label>
            <input type="text" name="registration_number" id="registration_number" value="<?= clean($org['registration_number'] ?? '') ?>" placeholder="e.g. from your 12A/80G or Society registration">
            <p class="help-text">Shown publicly so donors can verify your organization is a registered entity.</p>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label for="email">Public Email</label>
                <input type="text" name="email" id="email" value="<?= clean($org['email'] ?? '') ?>">
            </div>
            <div class="form-group">
                <label for="mobile_number">Phone Number</label>
                <input type="text" name="mobile_number" id="mobile_number" value="<?= clean($org['mobile_number'] ?? '') ?>">
            </div>
        </div>
        <div class="form-group">
            <label for="whatsapp_number">WhatsApp Number</label>
            <input type="text" name="whatsapp_number" id="whatsapp_number" value="<?= clean($org['whatsapp_number'] ?? '') ?>" placeholder="10-digit number, no country code">
            <p class="help-text">Shows a "Chat on WhatsApp" button on the homepage.</p>
        </div>

        <button type="submit" class="btn btn-primary btn-block">Save Changes</button>
    </form>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
