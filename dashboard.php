<?php
session_start();
include "connection.php";

if (!isset($_SESSION['farmer_id'])) {
    header("Location: login.php");
    exit();
}

$farmer_id = $_SESSION['farmer_id'];
$username = $_SESSION['name'];


/* TOTAL SALES */
$query = "
SELECT COUNT(*) AS total_sales
FROM sales
WHERE farmer_id = '$farmer_id'
";

$result = mysqli_query($conn, $query);

$row = mysqli_fetch_assoc($result);

$totalSales = $row['total_sales'] ?? 0;


/* TOTAL MONEY MADE THIS WEEK */
$query = "
SELECT SUM(sale_amount) AS weekly_total
FROM sales
WHERE farmer_id = '$farmer_id'
AND YEARWEEK(sale_date, 1) = YEARWEEK(CURDATE(), 1)
";

$result = mysqli_query($conn, $query);

$row = mysqli_fetch_assoc($result);

$weeklyTotal = $row['weekly_total'] ?? 0;


/* USER RATING */
$query = "
SELECT rating
FROM farmer
WHERE farmer_id = '$farmer_id'
";

$result = mysqli_query($conn, $query);
$row = mysqli_fetch_assoc($result);
$userRating = $row['rating'] ?? 0;


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
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Sans:ital,wght@0,300;0,400;0,500;1,300&display=swap" rel="stylesheet">
    <link href="collective_style.css" rel="stylesheet">
    <title>Dashboard</title>


</head>
<nav class="nav">
    <div class="logo"><span class="logo-dot"></span>HarvestPulse</div>
    <div class="nav-tabs">

        <a class="nav-tab" href="index.php">Home</a>
        <a class="nav-tab" href="browse_auction.php">Browse Auctions</a>
        <a class="nav-tab active" href="dashboard.php">Farmer Dashboard</a>
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
            <?php echo $_SESSION['name']; ?>
        </div>
    </div>
</nav>

<body>

    <div class="dashboard-layout">

        <!-- SIDE NAV -->
        <aside class="side-nav">

            <div class="side-title">

            </div>

            <a href="dashboard.php" class="side-link active">
                📊Dashboard
            </a>

            <a href="post_a_harvest.php" class= "side-link">
                ➕ Post a Harvest
            </a>

            <a href="active_listings.php" class="side-link">
                🔥Active Listings
            </a>

            <a href="sales_history.php" class="side-link">
                📋 Sales History
            </a>


            <a href="logout.php" class="side-link logout">
                🚪 Logout
            </a>

        </aside>


        <!-- MAIN CONTENT -->
        <main class="dashboard-content">

            <div class="metric-row">


                <!-- WEEKLY MONEY -->
                <div class="metric-card">

                    <div class="metric-icon">💰</div>

                    <div class="mc-label">
                        THIS WEEK
                    </div>

                    <div class="mc-value">
                        R<?php echo number_format($weeklyTotal, 0); ?>
                    </div>

                    <div class="mc-sub green">
                        Total earnings this week
                    </div>

                </div>
                <!-- TOTAL SALES -->
                <div class="metric-card">

                    <div class="metric-icon">💰</div>

                    <div class="mc-label">
                        TOTAL SALES
                    </div>

                    <div class="mc-value">
                        <?php echo $totalSales; ?>
                    </div>

                    <div class="mc-sub green">
                        Sales completed
                    </div>

                </div>





                <!-- USER RATING -->
                <div class="metric-card">

                    <div class="metric-icon">⭐</div>

                    <div class="mc-label">
                        BUYER RATING
                    </div>

                    <div class="mc-value">
                        <?php echo number_format($userRating, 1); ?>
                    </div>

                    <div class="mc-sub green">
                        Farmer performance rating
                    </div>
                </div>
            </div>
        </main>
    </div>

</body>

</html>