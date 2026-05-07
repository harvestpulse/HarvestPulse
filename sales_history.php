<?php
session_start();

if (!isset($_SESSION['name'])) {
    header("Location: login.php");
    exit();
}

$username = $_SESSION['name'];
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Sans:ital,wght@0,300;0,400;0,500;1,300&display=swap" rel="stylesheet">
    <link href="collective_style.css" rel="stylesheet">
    <title>sales History</title>


</head>
<nav class="nav">
    <div class="logo"><span class="logo-dot"></span>HarvestPulse</div>
    <div class="nav-tabs">

        <a class="nav-tab" href="index.php">Home</a>
        <a class="nav-tab" href="browse_auction.php">Browse Auctions</a>
        <a class="nav-tab active" href="dashboard.php">Farmer Dashboard</a>
    </div>
    <div class="nav-right">
        <div class="user-chip">

            <div class="user-av">
                <?php echo strtoupper(substr($_SESSION['name'], 0, 1)); ?>
            </div>

            <?php echo $_SESSION['name']; ?>

        </div>
    </div>
</nav>




<body>
    <script src="post_a_harvest.js">

    </script>


    <div class="dashboard-layout">

        <!-- SIDE NAV -->
        <aside class="side-nav">
            <div class="side-title">
            </div>
            <a href="dashboard.php" class="side-link">
                📊Dashboard
            </a>

            <a href="post_a_harvest.php" class="side-link">
                ➕ Post a Harvest
            </a>

            <a href="active_listings.php" class="side-link">
                🔥Active Listings
            </a>

            <a href="sales_history.php" class="side-link active">
                📋 Sales History
            </a>


            <a href="logout.php" class="side-link logout">
                🚪 Logout
            </a>
        </aside>


        <!-- MAIN CONTENT -->
        <main class="dashboard-content">

            <div class="card-panel">
            </div>

    </main>

    </div>

</body>

</html>