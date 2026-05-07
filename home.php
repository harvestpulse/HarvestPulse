<?php
session_start();

if (isset($_SESSION['farmer_id'])) {
    header("Location: farmer/dashboard.php");
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>HarvestPulse</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>

<div class="container">
    <h1>🌾 HarvestPulse</h1>
    <p>Fresh produce auctions directly from farmers.</p>

    <a href="login.php" class="btn">Login</a>
</div>

</body>
</html>