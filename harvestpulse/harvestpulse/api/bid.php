<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');
require_once '../includes/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['error' => 'POST only']); exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$listing_id = (int)($data['listing_id'] ?? 0);
$bidder_id  = (int)($data['bidder_id']  ?? 0);
$amount     = (float)($data['amount']   ?? 0);

if (!$listing_id || !$bidder_id || $amount <= 0) {
    echo json_encode(['error' => 'Missing fields']); exit;
}

$db = getDB();

// Lock listing row
$db->begin_transaction();

$stmt = $db->prepare("SELECT id, current_bid, status, ends_at FROM listings WHERE id = ? FOR UPDATE");
$stmt->bind_param('i', $listing_id);
$stmt->execute();
$listing = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$listing) {
    $db->rollback();
    echo json_encode(['error' => 'Listing not found']); exit;
}
if ($listing['status'] !== 'live') {
    $db->rollback();
    echo json_encode(['error' => 'Auction has ended']); exit;
}
if (strtotime($listing['ends_at']) < time()) {
    $db->query("UPDATE listings SET status='expired' WHERE id=$listing_id");
    $db->commit();
    echo json_encode(['error' => 'Auction has expired']); exit;
}
if ($amount <= $listing['current_bid']) {
    $db->rollback();
    echo json_encode(['error' => 'Bid must be higher than current bid of R' . number_format($listing['current_bid'], 2)]); exit;
}

// Insert bid
$stmt = $db->prepare("INSERT INTO bids (listing_id, bidder_id, amount) VALUES (?, ?, ?)");
$stmt->bind_param('iid', $listing_id, $bidder_id, $amount);
$stmt->execute();
$stmt->close();

// Update listing current bid
$stmt = $db->prepare("UPDATE listings SET current_bid = ? WHERE id = ?");
$stmt->bind_param('di', $amount, $listing_id);
$stmt->execute();
$stmt->close();

$db->commit();

// Return updated bid count
$result = $db->query("SELECT COUNT(*) as cnt FROM bids WHERE listing_id = $listing_id");
$cnt = $result->fetch_assoc()['cnt'];

echo json_encode([
    'success'     => true,
    'new_bid'     => $amount,
    'bid_count'   => (int)$cnt,
    'message'     => 'Bid placed successfully!'
]);

$db->close();
?>
