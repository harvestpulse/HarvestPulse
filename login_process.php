<?php
session_start();
include("connection.php");

$id_number = $_POST['id_number'] ?? '';


$sql = "SELECT * FROM farmer WHERE id_number = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $id_number);
$stmt->execute();

$result = $stmt->get_result();

if ($result->num_rows === 1) {

    $user = $result->fetch_assoc();

    // login success (NO PASSWORD CHECK)
    $_SESSION['farmer_id'] = $user['farmer_id'];
    $_SESSION['name'] = $user['name'];

    header("Location: dashboard.php");
    exit();

} else {
    header("Location: login.php?error=ID not found");
    exit();
}
?>