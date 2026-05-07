<?php
header('Content-Type: application/json');
$origin = $_SERVER['HTTP_ORIGIN'] ?? '';
$allowedOrigins = ['http://localhost:8000', 'http://127.0.0.1:8000'];
if (in_array($origin, $allowedOrigins, true)) {
    header("Access-Control-Allow-Origin: $origin");
}
require_once '../includes/db.php';

$listing_id = (int)($_GET['listing_id'] ?? 0);
if (!$listing_id) { echo json_encode(['error' => 'Missing listing_id']); exit; }

$db = getDB();

$stmt = $db->prepare("
    SELECT b.amount, b.created_at,
           u.name AS bidder_name, u.location AS bidder_location,
           u.avatar_initials, u.avatar_color, u.avatar_text_color
    FROM bids b
    JOIN users u ON u.id = b.bidder_id
    WHERE b.listing_id = ?
    ORDER BY b.created_at DESC
    LIMIT 8
");
$stmt->bind_param('i', $listing_id);
$stmt->execute();
$result = $stmt->get_result();
$bids = [];

while ($row = $result->fetch_assoc()) {
    $row['amount'] = (float)$row['amount'];
    $bids[] = $row;
}

$stmt->close();
$db->close();

echo json_encode(['success' => true, 'bids' => $bids]);
