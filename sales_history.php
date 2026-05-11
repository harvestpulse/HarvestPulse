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
WHERE farmer_id = ?
";

$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "i", $farmer_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$row = mysqli_fetch_assoc($result);

$totalSales = $row['total_sales'] ?? 0;


/* TOTAL REVENUE */
$query = "
SELECT SUM(sale_amount) AS total_revenue
FROM sales
WHERE farmer_id = ?
";

$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "i", $farmer_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$row = mysqli_fetch_assoc($result);

$totalRevenue = $row['total_revenue'] ?? 0;


/* GET SALES */
$query = "
SELECT *
FROM sales
WHERE farmer_id = ?
ORDER BY sale_date DESC
";

$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "i", $farmer_id);
mysqli_stmt_execute($stmt);

$sales = mysqli_stmt_get_result($stmt);

$query = "
SELECT COUNT(*) AS total_listings
FROM harvest
WHERE farmer_id = ?
";

$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "i", $farmer_id);
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

            <a href="dashboard.php" class="side-link">
                📊Dashboard
            </a>

            <a href="post_a_harvest.php" class= "side-link">
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

        <main class="dashboard-content">
            <div class="page">

    <div class="page-top">
        <div>
            <div class="page-title">Sales History</div>
        </div>
    </div>


    <div class="stats-grid">

        <div class="stat-card">
            <div class="stat-label">Total Sales</div>
            <div class="stat-value">
                <?php echo $totalSales; ?>
            </div>
        </div>


        <div class="stat-card">
            <div class="stat-label">Total Revenue</div>
            <div class="stat-value">
                R<?php echo number_format($totalRevenue, 2); ?>
            </div>
        </div>

    </div>


    <div class="sales-panel">

        <div class="panel-title">
            Recent Sales
        </div>

        <table>

            <thead>
                <tr>
                    <th>#</th>
                    <th>Produce</th>
                    <th>Sale Date</th>
                    <th>Day</th>
                    <th>Amount</th>
                </tr>
            </thead>

            <tbody>

                <?php if(mysqli_num_rows($sales) > 0) : ?>

                    <?php while($row = mysqli_fetch_assoc($sales)) : ?>

                        <tr>

                            <td>
                                #<?php echo $row['sale_id']; ?>
                            </td>

                            <td>
                                <span class="produce-tag">
                                    <?php echo htmlspecialchars($row['produce_name']); ?>
                                </span>
                            </td>

                            <td>
                                <?php echo htmlspecialchars($row['sale_date']); ?>
                            </td>

                            <td>
                                <?php echo htmlspecialchars($row['day_name']); ?>
                            </td>

                            <td class="price">
                                R<?php echo number_format($row['sale_amount'], 2); ?>
                            </td>

                        </tr>

                    <?php endwhile; ?>

                <?php else : ?>

                    <tr>
                        <td colspan="5">

                            <div class="empty-state">
                                No sales found.
                            </div>

                        </td>
                    </tr>

                <?php endif; ?>

            </tbody>

        </table>

    </div>

</div>

           
        
        </main>

            </div>

</body>

</html>