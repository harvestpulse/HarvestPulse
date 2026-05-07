<?php
session_start();
include "connection.php";

if (!isset($_SESSION['farmer_id'])) {
    header("Location: login.php");
    exit();
}


$farmer_id = $_SESSION['farmer_id'];

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

$produce = [
    ['e' => '🍅', 'n' => 'Tomatoes'],
    ['e' => '🥬', 'n' => 'Spinach'],
    ['e' => '🌽', 'n' => 'Corn'],
    ['e' => '🎃', 'n' => 'Butternut'],
    ['e' => '🥕', 'n' => 'Carrots'],
    ['e' => '🥦', 'n' => 'Broccoli'],
    ['e' => '🧅', 'n' => 'Onions'],
    ['e' => '🥔', 'n' => 'Potatoes'],
    ['e' => '🫑', 'n' => 'Peppers'],
    ['e' => '🍆', 'n' => 'Brinjal'],
    ['e' => '🌿', 'n' => 'Herbs'],
    ['e' => '🍋', 'n' => 'Lemons'],
    ['e' => '🥭', 'n' => 'Mangoes'],
    ['e' => '🍓', 'n' => 'Berries'],
    ['e' => '🫛', 'n' => 'Peas'],
    ['e' => '🌰', 'n' => 'Nuts'],
    ['e' => '🍠', 'n' => 'Sweet pot'],
    ['e' => '🥒', 'n' => 'Cucumber'],
    ['e' => '🧄', 'n' => 'Garlic'],
    ['e' => '🌶️', 'n' => 'Chilli']
];


if (isset($_POST['go_live'])) {

    $farmer_id = $_SESSION['farmer_id'];
    $username = $_SESSION['name'];

    $crop_name = $_POST['crop_name'];
    $description = $_POST['description'];
    $price = $_POST['price'];
    $auction_window = $_POST['auction_window'];
    $pickup_note = $_POST['pickup_note'];

    // Get farmer location from farmer table
    $locationQuery = "
        SELECT location
        FROM farmer
        WHERE farmer_id = ?
    ";

    $stmt = mysqli_prepare($conn, $locationQuery);
    mysqli_stmt_bind_param($stmt, "i", $farmer_id);
    mysqli_stmt_execute($stmt);
    $locationResult = mysqli_stmt_get_result($stmt);
    $farmer = mysqli_fetch_assoc($locationResult);

    $location = $farmer['location'];

    // Insert into harvest table
    $insertQuery = "
        INSERT INTO harvest 
        (farmer_id, username, crop_name, description, price)
        VALUES (?, ?, ?, ?, ?)
    ";

    $stmt = mysqli_prepare($conn, $insertQuery);

    mysqli_stmt_bind_param(
        $stmt,
        "isssd",
        $farmer_id,
        $username,
        $crop_name,
        $description,
        $price
    );

    if (mysqli_stmt_execute($stmt)) {
        echo "<script>
            alert('Harvest posted successfully!');
            window.location.href='active_listings.php';
        </script>";
    } else {
        echo "<script>alert('Error posting harvest');</script>";
    }
}
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

        <a class="nav-tab" href="index.html">Home</a>
        <a class="nav-tab" href="browseAuctions.html">Browse Auctions</a>
        <a class="nav-tab active" href="farmerDashboard.html">Farmer Dashboard</a>
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

            <a href="post_a_harvest.php" class="side-link active">
                ➕ Post a Harvest
            </a>

            <a href="active_listings.php" class="side-link">
                🔥Active Listings
            </a>

            <a href="#" class="side-link">
                📋 Sales History
            </a>


            <a href="logout.php" class="side-link logout">
                🚪 Logout
            </a>
        </aside>


        <!-- MAIN CONTENT -->
        <main class="dashboard-content">


            <form method="POST" action="post_a_harvest.php">

    <div class="card-panel">

        <div class="form-group">
            <label class="form-label">What are you selling today?</label>

            <div class="produce-grid">
                <?php foreach ($produce as $index => $item): ?>
                    <div class="produce-item <?php echo $index === 0 ? 'sel' : ''; ?>"
                         onclick="selectProduce(this, '<?php echo $item['n']; ?>')">

                        <div class="produce-emoji">
                            <?php echo $item['e']; ?>
                        </div>

                        <div class="produce-name">
                            <?php echo $item['n']; ?>
                        </div>

                    </div>
                <?php endforeach; ?>
            </div>

            <input type="hidden" name="crop_name" id="cropName" value="Tomatoes">
        </div>

        <div class="form-group">
            <label class="form-label">Describe your harvest</label>

            <input class="form-input"
                   type="text"
                   name="description"
                   placeholder="e.g. 12kg fresh tomatoes, picked this morning"
                   id="listingDesc"
                   required>
        </div>

        <div class="form-group">
            <label class="form-label">Minimum price you'll accept</label>

            <div class="range-row">
                <input class="form-range"
                       type="range"
                       name="price"
                       min="10"
                       max="300"
                       step="5"
                       value="45"
                       oninput="document.getElementById('rval').textContent='R'+this.value">

                <div class="range-val" id="rval">R45</div>
            </div>
        </div>

        <div class="form-group">
            <label class="form-label">Auction window</label>

            <div class="window-row">
                <button type="button" class="window-btn" onclick="selectWindow(this,'15 min')">15 min</button>
                <button type="button" class="window-btn sel" onclick="selectWindow(this,'30 min')">30 min</button>
                <button type="button" class="window-btn" onclick="selectWindow(this,'1 hour')">1 hour</button>
                <button type="button" class="window-btn" onclick="selectWindow(this,'2 hours')">2 hours</button>
            </div>

            <input type="hidden" name="auction_window" id="auctionWindow" value="30 min">
        </div>

        <div class="form-group">
            <label class="form-label">Pickup / delivery notes</label>

            <input class="form-input"
                   type="text"
                   name="pickup_note"
                   placeholder="e.g. Farm gate pickup"
                   id="pickupNote">
        </div>

        <button type="submit" name="go_live" class="go-live-btn">
            🔥 Go Live Now
        </button>

    </div>

</form>
    </div>


    </div>


    </main>

    </div>

</body>

</html>