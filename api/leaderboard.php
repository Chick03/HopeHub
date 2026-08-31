<?php
/**
 * GET /hopehub/api/leaderboard.php
 *
 * Returns the top donors ranked by total successful cash donations, as JSON.
 * This is the "Web Application Server (PHP) — CRUD Operations / Business
 * Logic" tier from the System Architecture diagram: the frontend (plain
 * JS on index.php) calls this endpoint with fetch() and renders the result
 * itself, instead of the server pre-rendering the HTML.
 *
 * No session/login required — this is public trust/transparency info,
 * same as a real crowdfunding platform's donor leaderboard.
 */
require_once __DIR__ . '/../config/db.php';

header('Content-Type: application/json');

try {
    $stmt = $pdo->query(
        "SELECT u.name, SUM(d.amount) AS total_donated, COUNT(d.donation_id) AS num_donations
         FROM donations d
         JOIN users u ON u.user_id = d.donor_id
         WHERE d.donation_type = 'Cash' AND d.status IN ('success', 'verified')
         GROUP BY d.donor_id
         ORDER BY total_donated DESC
         LIMIT 10"
    );
    $rows = $stmt->fetchAll();

    $leaderboard = array_map(function ($row, $index) {
        return [
            'rank'          => $index + 1,
            'name'          => $row['name'],
            'total_donated' => (float)$row['total_donated'],
            'num_donations' => (int)$row['num_donations'],
        ];
    }, $rows, array_keys($rows));

    echo json_encode(['success' => true, 'leaderboard' => $leaderboard]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Could not load leaderboard.']);
}
