<?php
session_start();
include "connection.php";

if (!isset($_SESSION['name'])) {
    header("Location: login.php");
    exit();
}

$username = $_SESSION['name'];


/* TOTAL LISTINGS */
$query = "
SELECT COUNT(*) AS total_listings
FROM harvest
WHERE username = ?
";

$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "s", $username);
mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);
$row = mysqli_fetch_assoc($result);

$totalListings = $row['total_listings'] ?? 0;


/* GET HARVESTS WITH FARMER LOCATION */
$query = "
SELECT 
    harvest.*,
    farmer.location
FROM harvest
INNER JOIN farmer
ON harvest.farmer_id = farmer.farmer_id
WHERE harvest.username = ?
ORDER BY harvest.created_at DESC
";

$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "s", $username);
mysqli_stmt_execute($stmt);

$harvests = mysqli_stmt_get_result($stmt);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Sans:ital,wght@0,300;0,400;0,500;1,300&display=swap" rel="stylesheet">
    <link href="collective_style.css" rel="stylesheet">

    <title>Browse Auctions</title>

    
</head>

<body>

<nav class="nav">
    <div class="logo"><span class="logo-dot"></span>HarvestPulse</div>

    <div class="nav-tabs">
        <a class="nav-tab" href="index.php">Home</a>
        <a class="nav-tab active" href="browse_auction.php">Browse Auctions</a>
        <a class="nav-tab" href="dashboard.php">Farmer Dashboard</a>
    </div>

    <div class="nav-right">
        <div class="live-badge">
            <span class="live-dot"></span>
            <span id="liveCount"><?php echo $totalListings; ?> live listings</span>
        </div>

        <div class="user-chip">
            <div class="user-av">
                <?php echo strtoupper(substr($_SESSION['name'], 0, 1)); ?>
            </div>
            <?php echo htmlspecialchars($_SESSION['name']); ?>
        </div>
    </div>
</nav>


<div class="auctions-panel">

    <!-- LEFT SIDE -->
    <div class="panel left-panel">

        <div class="panel-title">Live Auctions</div>
        <div class="panel-sub">Gauteng region · Updated every few seconds</div>

        <div class="auction-grid">

            <?php while ($row = mysqli_fetch_assoc($harvests)) : ?>

                <?php
                    $farmName = htmlspecialchars($row['username']);
                    $location = htmlspecialchars($row['location']);
                    $description = htmlspecialchars($row['description']);
                    $crop = htmlspecialchars($row['crop_name']);
                    $price = number_format($row['price'], 2);
                ?>

                

                <div class="auction-card">

                <div class="card-img">
                        🍅
                    </div>
                        <div class="info">
                            <?php echo $farmName; ?>
                        </div>

                        <div class="info">
                            📍 <?php echo $location; ?>
                        </div>

                        <div class="info">
                            <?php echo $description; ?>
                        </div>

                        <div class="info">
                            <span class="tag">
                                <?php echo $crop; ?>
                            </span>
                        </div>

                        <div class="info">
                            R<?php echo $price; ?>
                        </div>
                </div>

            <?php endwhile; ?>

        </div>

    </div>


    <!-- RIGHT SIDE -->
    <div class="panel right-panel">

        <h2>Bid Panel</h2>

        <p>Total Listings</p>

        <h1><?php echo $totalListings; ?></h1>

    </div>

</div>

</body>
</html>