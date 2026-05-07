<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
require_once '../includes/db.php';

$farmer_id = (int)($_GET['farmer_id'] ?? 1);
$db = getDB();

// Weekly earnings
$stmt = $db->prepare("
    SELECT COALESCE(SUM(l.current_bid), 0) AS week_earnings
    FROM listings l
    WHERE l.farmer_id = ? AND l.status = 'sold' AND l.ends_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
");
$stmt->bind_param('i', $farmer_id);
$stmt->execute();
$week = $stmt->get_result()->fetch_assoc();
$stmt->close();

// Total auctions sold
$stmt = $db->prepare("SELECT COUNT(*) AS total_sold FROM listings WHERE farmer_id = ? AND status = 'sold'");
$stmt->bind_param('i', $farmer_id);
$stmt->execute();
$sold = $stmt->get_result()->fetch_assoc();
$stmt->close();

// Active listings
$stmt = $db->prepare("SELECT COUNT(*) AS active FROM listings WHERE farmer_id = ? AND status = 'live'");
$stmt->bind_param('i', $farmer_id);
$stmt->execute();
$active = $stmt->get_result()->fetch_assoc();
$stmt->close();

// Active listings detail
$stmt = $db->prepare("
    SELECT l.id, l.emoji, l.title, l.current_bid, l.ends_at,
           TIMESTAMPDIFF(SECOND, NOW(), l.ends_at) AS seconds_left,
           COUNT(b.id) AS bid_count
    FROM listings l
    LEFT JOIN bids b ON b.listing_id = l.id
    WHERE l.farmer_id = ? AND l.status = 'live'
    GROUP BY l.id ORDER BY l.ends_at ASC
");
$stmt->bind_param('i', $farmer_id);
$stmt->execute();
$activeResult = $stmt->get_result();
$activeListings = [];
while ($r = $activeResult->fetch_assoc()) {
    $r['seconds_left'] = max(0, (int)$r['seconds_left']);
    $r['current_bid'] = (float)$r['current_bid'];
    $r['bid_count'] = (int)$r['bid_count'];
    $activeListings[] = $r;
}
$stmt->close();

// Sales history
$stmt = $db->prepare("
    SELECT l.emoji, l.title, l.current_bid, l.ends_at, l.status,
           COUNT(b.id) AS bid_count
    FROM listings l
    LEFT JOIN bids b ON b.listing_id = l.id
    WHERE l.farmer_id = ?
    GROUP BY l.id ORDER BY l.ends_at DESC LIMIT 10
");
$stmt->bind_param('i', $farmer_id);
$stmt->execute();
$histResult = $stmt->get_result();
$history = [];
while ($r = $histResult->fetch_assoc()) {
    $r['current_bid'] = (float)$r['current_bid'];
    $r['bid_count'] = (int)$r['bid_count'];
    $history[] = $r;
}
$stmt->close();

// Daily earnings chart (last 7 days)
$stmt = $db->prepare("
    SELECT DATE(ends_at) AS day, COALESCE(SUM(current_bid),0) AS earned
    FROM listings
    WHERE farmer_id = ? AND status = 'sold' AND ends_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
    GROUP BY DATE(ends_at) ORDER BY day ASC
");
$stmt->bind_param('i', $farmer_id);
$stmt->execute();
$chartResult = $stmt->get_result();
$chart = [];
while ($r = $chartResult->fetch_assoc()) $chart[] = $r;
$stmt->close();

$db->close();

echo json_encode([
    'success'         => true,
    'week_earnings'   => (float)$week['week_earnings'],
    'total_sold'      => (int)$sold['total_sold'],
    'active_count'    => (int)$active['active'],
    'active_listings' => $activeListings,
    'history'         => $history,
    'chart'           => $chart
]);
?>
