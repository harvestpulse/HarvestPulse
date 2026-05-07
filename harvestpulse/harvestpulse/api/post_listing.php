<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');
require_once '../includes/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['error' => 'POST only']); exit;
}

$data        = json_decode(file_get_contents('php://input'), true);
$farmer_id   = (int)($data['farmer_id']    ?? 0);
$emoji       = $data['emoji']              ?? '🌿';
$title       = trim($data['title']         ?? '');
$description = trim($data['description']   ?? '');
$weight_kg   = (float)($data['weight_kg']  ?? 0);
$reserve     = (float)($data['reserve']    ?? 0);
$retail      = (float)($data['retail']     ?? 0);
$minutes     = (int)($data['minutes']      ?? 30);
$pickup      = trim($data['pickup']        ?? '');

if (!$farmer_id || !$title || $reserve <= 0) {
    echo json_encode(['error' => 'Missing required fields']); exit;
}

$db = getDB();
$ends_at = date('Y-m-d H:i:s', strtotime("+{$minutes} minutes"));

$stmt = $db->prepare("
    INSERT INTO listings (farmer_id, emoji, title, description, weight_kg, reserve_price, current_bid, retail_value, status, ends_at, pickup_notes)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'live', ?, ?)
");
$stmt->bind_param('isssddddss', $farmer_id, $emoji, $title, $description, $weight_kg, $reserve, $reserve, $retail, $ends_at, $pickup);
$stmt->execute();
$new_id = $stmt->insert_id;
$stmt->close();
$db->close();

echo json_encode(['success' => true, 'listing_id' => $new_id, 'message' => 'Listing is now live!']);
?>
