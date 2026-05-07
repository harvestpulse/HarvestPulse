<?php
header('Content-Type: application/json');
$origin = $_SERVER['HTTP_ORIGIN'] ?? '';
$allowedOrigins = ['http://localhost:8000', 'http://127.0.0.1:8000'];
if (in_array($origin, $allowedOrigins, true)) {
    header("Access-Control-Allow-Origin: $origin");
}
require_once '../includes/db.php';

$db = getDB();

// Auto-expire listings whose time has passed
$db->query("UPDATE listings SET status='expired' WHERE status='live' AND ends_at < NOW()");

$sql = "
    SELECT
        l.id, l.emoji, l.title, l.description, l.weight_kg,
        l.reserve_price, l.current_bid, l.retail_value,
        l.status, l.ends_at, l.pickup_notes,
        u.name AS farmer_name, u.location AS farmer_location,
        TIMESTAMPDIFF(SECOND, NOW(), l.ends_at) AS seconds_left,
        COUNT(b.id) AS bid_count
    FROM listings l
    JOIN users u ON u.id = l.farmer_id
    LEFT JOIN bids b ON b.listing_id = l.id
    WHERE l.status = 'live'
    GROUP BY l.id
    ORDER BY l.ends_at ASC
";

$result = $db->query($sql);
$listings = [];

while ($row = $result->fetch_assoc()) {
    $row['seconds_left'] = max(0, (int)$row['seconds_left']);
    $row['current_bid'] = (float)$row['current_bid'];
    $row['reserve_price'] = (float)$row['reserve_price'];
    $row['retail_value'] = (float)$row['retail_value'];
    $row['bid_count'] = (int)$row['bid_count'];
    $listings[] = $row;
}

echo json_encode(['success' => true, 'listings' => $listings]);
$db->close();
