<?php
session_start();
include "connection.php";

if (!isset($_SESSION['farmer_id'])) {
    header("Location: login.php");
    exit();
}

$farmer_id = $_SESSION['farmer_id'];

/* DELETE LISTING */
if (isset($_POST['delete_listing'])) {
    $harvest_id = $_POST['harvest_id'];

    $deleteQuery = "
    DELETE FROM harvest
    WHERE harvest_id = ?
    AND farmer_id = ?
    ";

    $deleteStmt = mysqli_prepare($conn, $deleteQuery);
    mysqli_stmt_bind_param($deleteStmt, "ii", $harvest_id, $farmer_id);
    mysqli_stmt_execute($deleteStmt);

    header("Location: active_listings.php");
    exit();
}

/* GET ACTIVE LISTINGS */
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
$totalListings = mysqli_num_rows($harvests);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Active Listings</title>

    <link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Sans:ital,wght@0,300;0,400;0,500;1,300&display=swap" rel="stylesheet">
    <link href="collective_style.css" rel="stylesheet">

    <style>
        .delete-listing-btn {
            margin-top: 8px;
            background: #ef4444;
            color: white;
            border: none;
            padding: 8px 14px;
            border-radius: 10px;
            font-weight: 700;
            cursor: pointer;
        }

        .delete-listing-btn:hover {
            background: #dc2626;
        }
    </style>
</head>

<body>

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
            <?php echo htmlspecialchars($_SESSION['name']); ?>
        </div>
    </div>
</nav>

<div class="dashboard-layout">

    <aside class="side-nav">
        <div class="side-title"></div>

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

    <main class="dashboard-content">

        <div class="panel-title">Active Listings</div>
        <div class="panel-sub"><?php echo $totalListings; ?> auctions running · Updated live</div>

        <div class="card-panel">

            <?php if (mysqli_num_rows($harvests) > 0): ?>

                <?php while ($row = mysqli_fetch_assoc($harvests)): ?>

                    <?php
                    $crop = htmlspecialchars($row['crop_name']);

                    $cropIcons = [
                        "Tomatoes" => "🍅",
                        "Spinach" => "🥬",
                        "Corn" => "🌽",
                        "Butternut" => "🎃",
                        "Carrots" => "🥕",
                        "Broccoli" => "🥦",
                        "Onions" => "🧅",
                        "Potatoes" => "🥔",
                        "Peppers" => "🫑",
                        "Brinjal" => "🍆",
                        "Herbs" => "🌿",
                        "Lemons" => "🍋",
                        "Mangoes" => "🥭",
                        "Berries" => "🍓",
                        "Peas" => "🫛",
                        "Nuts" => "🌰",
                        "Sweet potato" => "🍠",
                        "Cucumber" => "🥒",
                        "Garlic" => "🧄"
                    ];

                    $icon = $cropIcons[$crop] ?? "🌱";
                    ?>

                    <div class="listing-row">

                        <div class="lr-emoji">
                            <?php echo $icon; ?>
                        </div>

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
                                <?php echo $crop; ?> · Posted:
                                <?php echo htmlspecialchars($row['created_at']); ?>
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

                            <form method="POST" onsubmit="return confirm('Are you sure you want to remove this listing?');">
                                <input type="hidden" name="harvest_id" value="<?php echo $row['harvest_id']; ?>">

                                <button type="submit" name="delete_listing" class="delete-listing-btn">
                                    Remove
                                </button>
                            </form>
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