<?php
session_start();
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth_check.php';
require_admin();

$pageTitle = 'Reports & Analytics';

$startDate = $_GET['start'] ?? date('Y-m-01', strtotime('-5 months'));
$endDate   = $_GET['end'] ?? date('Y-m-d');

// Totals within the range
$totalsStmt = $pdo->prepare(
    "SELECT COUNT(*) AS num, COALESCE(SUM(CASE WHEN donation_type='Cash' THEN amount ELSE 0 END),0) AS total_cash
     FROM donations WHERE status IN ('success','verified') AND DATE(created_at) BETWEEN ? AND ?"
);
$totalsStmt->execute([$startDate, $endDate]);
$totals = $totalsStmt->fetch();

// By category
$byCategoryStmt = $pdo->prepare(
    "SELECT donation_type, COUNT(*) AS num FROM donations
     WHERE status IN ('success','verified') AND DATE(created_at) BETWEEN ? AND ?
     GROUP BY donation_type"
);
$byCategoryStmt->execute([$startDate, $endDate]);
$byCategory = $byCategoryStmt->fetchAll();

// Needs fulfillment (replaces "fund utilization by orphanage" — there's only one orphanage now)
$needsStmt = $pdo->query(
    "SELECT status, COUNT(*) AS num FROM needs GROUP BY status"
);
$needsByStatus = ['open' => 0, 'fulfilled' => 0];
foreach ($needsStmt->fetchAll() as $row) {
    $needsByStatus[$row['status']] = (int)$row['num'];
}

// Monthly trend for chart
$monthlyStmt = $pdo->prepare(
    "SELECT DATE_FORMAT(created_at, '%Y-%m') AS ym, COALESCE(SUM(CASE WHEN donation_type='Cash' THEN amount ELSE 0 END),0) AS total_cash, COUNT(*) AS num
     FROM donations WHERE status IN ('success','verified') AND DATE(created_at) BETWEEN ? AND ?
     GROUP BY ym ORDER BY ym"
);
$monthlyStmt->execute([$startDate, $endDate]);
$monthly = $monthlyStmt->fetchAll();
$chartLabels = array_column($monthly, 'ym');
$chartValues = array_column($monthly, 'total_cash');

include __DIR__ . '/../includes/header.php';
?>

<h1>Reports & Analytics</h1>

<form method="get" style="display:flex; gap:12px; align-items:flex-end; margin-bottom:24px; flex-wrap:wrap;">
    <div class="form-group" style="margin:0;"><label for="start">From</label><input type="date" name="start" id="start" value="<?= clean($startDate) ?>"></div>
    <div class="form-group" style="margin:0;"><label for="end">To</label><input type="date" name="end" id="end" value="<?= clean($endDate) ?>"></div>
    <button type="submit" class="btn btn-secondary">Apply</button>
    <a href="/hopehub/admin/export_report.php?start=<?= urlencode($startDate) ?>&end=<?= urlencode($endDate) ?>" class="btn btn-outline">Export CSV</a>
</form>

<div class="stats-row">
    <div class="stat-card"><div class="stat-value"><?= (int)$totals['num'] ?></div><div class="stat-label">Donations in Range</div></div>
    <div class="stat-card"><div class="stat-value"><?= formatCurrency($totals['total_cash']) ?></div><div class="stat-label">Cash Raised in Range</div></div>
</div>

<h2>Monthly Donation Trend</h2>
<canvas id="trendChart" height="90"></canvas>

<div class="two-col" style="margin-top:36px;">
    <div>
        <h2>Needs Fulfillment</h2>
        <table>
            <tr><th>Status</th><th>Count</th></tr>
            <tr><td>Open</td><td><?= $needsByStatus['open'] ?></td></tr>
            <tr><td>Fulfilled</td><td><?= $needsByStatus['fulfilled'] ?></td></tr>
        </table>
        <p class="help-text" style="margin-top:10px;">Manage individual needs from <a href="/hopehub/admin/manage_content.php">Children & Needs</a>.</p>
    </div>
    <div>
        <h2>By Category</h2>
        <table>
            <tr><th>Type</th><th>Count</th></tr>
            <?php foreach ($byCategory as $c): ?>
            <tr><td><?= clean($c['donation_type']) ?></td><td><?= (int)$c['num'] ?></td></tr>
            <?php endforeach; ?>
        </table>
    </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.0/chart.umd.min.js"></script>
<script>
new Chart(document.getElementById('trendChart'), {
    type: 'bar',
    data: {
        labels: <?= json_encode($chartLabels) ?>,
        datasets: [{
            label: 'Cash Raised (₹)',
            data: <?= json_encode(array_map('floatval', $chartValues)) ?>,
            backgroundColor: '#E8A33D'
        }]
    },
    options: { responsive: true, plugins: { legend: { display: false } } }
});
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
