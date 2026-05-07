<?php
session_start();
include "connection.php";
$farmer_id = $_SESSION['farmer_id'];

$query = "
SELECT 
    harvest.harvest_id,
    harvest.description,
    harvest.price,
    harvest.username,
    harvest.crop_name,
    farmer.location,

    harvest.created_at

FROM harvest

INNER JOIN farmer
ON harvest.farmer_id = farmer.farmer_id

WHERE harvest.farmer_id = ?

ORDER BY harvest.created_at DESC
";

$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "i", $farmer_id);
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
    <title>Post a harvest</title>

</head>
<nav class="nav">
    <div class="logo"><span class="logo-dot"></span>HarvestPulse</div>
    <div class="nav-tabs">
        <a class="nav-tab" href="index.php">Home</a>
        <a class="nav-tab" href="browseAuctions.html">Browse Auctions</a>
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

            <a href="active_listings.php" class="side-link active">
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
            <div class="panel-title">Active Listings</div>
            <div class="panel-sub">[3] auctions running · Updated live</div>
            <div class="card-panel">

    <?php if (mysqli_num_rows($harvests) > 0): ?>
        <?php while ($row = mysqli_fetch_assoc($harvests)): ?>
            <div class="listing-row">
                <div class="lr-emoji">🌱</div>
                <div class="lr-info">
                    <div class="lr-title">
                        <?php echo htmlspecialchars($row['description']); ?>
                    </div>

                    <div class="lr-sub">
                        <?php echo htmlspecialchars($row['username']); ?>
                        ·
                        <?php echo htmlspecialchars($row['location']); ?>
                    </div>

                    <div class="lr-bids">
                        Posted: <?php echo htmlspecialchars($row['created_at']); ?>
                    </div>
                </div>

                <div style="text-align:right;">
                    <div class="lr-timer">00:00</div>

                    <div class="lr-price">
                        R<?php echo htmlspecialchars($row['price']); ?>
                    </div>

                    <div style="font-size:10px;color:var(--green);margin-top:2px;">
                        current price
                    </div>
                </div>

            </div>
        <?php endwhile; ?>
    <?php else: ?>
        <p>No harvests posted yet.</p>
    <?php endif; ?>

</div>
        </main>
    </div>
</body>

</html>