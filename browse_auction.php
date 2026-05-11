<?php
session_start();
include "connection.php";

if (!isset($_SESSION['name'])) {
    header("Location: login.php");
    exit();
}

$username = $_SESSION['name'];

/* BUY NOW - REMOVE LISTING */
if (isset($_POST['buy_now'])) {
    $harvest_id = $_POST['harvest_id'];

    $deleteQuery = "
    DELETE FROM harvest
    WHERE harvest_id = ?
    ";

    $deleteStmt = mysqli_prepare($conn, $deleteQuery);
    mysqli_stmt_bind_param($deleteStmt, "i", $harvest_id);
    mysqli_stmt_execute($deleteStmt);

    header("Location: browse_auction.php");
    exit();
}

/* TOTAL LISTINGS FROM EVERY FARMER */
$query = "
SELECT COUNT(*) AS total_listings
FROM harvest
";

$result = mysqli_query($conn, $query);
$row = mysqli_fetch_assoc($result);

$totalListings = $row['total_listings'] ?? 0;

/* GET ALL HARVESTS FROM EVERY FARMER WITH LOCATION */
$query = "
SELECT 
    harvest.*,
    farmer.location
FROM harvest
INNER JOIN farmer
ON harvest.farmer_id = farmer.farmer_id
ORDER BY harvest.created_at DESC
";

$harvests = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Browse Auctions</title>

    <link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Sans:ital,wght@0,300;0,400;0,500;1,300&display=swap" rel="stylesheet">
    <link href="collective_style.css" rel="stylesheet">

    <style>
        .auction-card {
            cursor: pointer;
            transition: 0.2s ease;
        }

        .auction-card:hover {
            transform: translateY(-4px);
        }

        .right-panel {
            width: 340px;
            max-height: 650px;
            padding: 22px;
            border-radius: 24px;
            position: sticky;
            top: 20px;
            overflow-y: auto;
        }

        .bid-detail-box {
            margin-top: 12px;
        }

        .selected-icon {
            font-size: 48px;
            margin-bottom: 10px;
        }

        .selected-title {
            font-size: 20px;
            font-weight: 800;
            margin-bottom: 6px;
        }

        .selected-info {
            color: #6b7280;
            margin-bottom: 7px;
            font-size: 13px;
        }

        .selected-price {
            font-size: 26px;
            font-weight: 800;
            color: var(--green);
            margin: 10px 0;
        }

        .time-box {
            background: #ecfdf5;
            border: 1px solid #bbf7d0;
            padding: 10px;
            border-radius: 14px;
            margin: 10px 0;
        }

        .time-label {
            font-size: 11px;
            color: #166534;
            font-weight: 700;
        }

        .time-value {
            font-size: 18px;
            font-weight: 800;
            color: #14532d;
            margin-top: 4px;
        }

        .bid-input {
            width: 100%;
            padding: 12px;
            border-radius: 12px;
            border: 1px solid #d1d5db;
            margin-top: 10px;
            font-size: 14px;
        }

        .bid-btn,
        .buy-btn {
            width: 100%;
            padding: 12px;
            border: none;
            border-radius: 14px;
            font-weight: 800;
            cursor: pointer;
            margin-top: 10px;
        }

        .bid-btn {
            background: #22c55e;
            color: white;
        }

        .buy-btn {
            background: #111827;
            color: white;
        }

        .bid-btn:hover {
            background: #16a34a;
        }

        .buy-btn:hover {
            background: #000;
        }

        .empty-bid-text {
            color: #6b7280;
            margin-top: 12px;
            line-height: 1.5;
            font-size: 14px;
        }
    </style>
</head>

<body>

<nav class="nav">
    <div class="logo">
        <span class="logo-dot"></span>HarvestPulse
    </div>

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

    <div class="panel left-panel">

        <div class="panel-title">Live Auctions</div>

        <div class="panel-sub">
            Gauteng region · Updated every few seconds
        </div>

        <div class="auction-grid">

            <?php if (mysqli_num_rows($harvests) > 0): ?>

                <?php while ($row = mysqli_fetch_assoc($harvests)) : ?>

                    <?php
                        $farmName = htmlspecialchars($row['username']);
                        $location = htmlspecialchars($row['location']);
                        $description = htmlspecialchars($row['description']);
                        $crop = htmlspecialchars($row['crop_name']);
                        $price = number_format($row['price'], 2);
                        $rawPrice = htmlspecialchars($row['price']);
                        $createdAt = htmlspecialchars($row['created_at']);

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

                        $icon = $cropIcons[$crop] ?? "🥬";
                    ?>

                    <div 
                        class="auction-card"
                        onclick="selectAuction(this)"
                        data-id="<?php echo $row['harvest_id']; ?>"
                        data-icon="<?php echo $icon; ?>"
                        data-farm="<?php echo $farmName; ?>"
                        data-location="<?php echo $location; ?>"
                        data-description="<?php echo $description; ?>"
                        data-crop="<?php echo $crop; ?>"
                        data-price="<?php echo $rawPrice; ?>"
                        data-created="<?php echo $createdAt; ?>"
                    >

                        <div class="card-img">
                            <?php echo $icon; ?>
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

            <?php else: ?>

                <p>No auctions available.</p>

            <?php endif; ?>

        </div>

    </div>

    <div class="panel right-panel">

        <h2>Bid Panel</h2>

        <p>Total Listings</p>
        <h1><?php echo $totalListings; ?></h1>

        <div class="bid-detail-box" id="bidPanel">
            <p class="empty-bid-text">
                Click on a listing to view bid time, place a bid, or buy immediately.
            </p>
        </div>

    </div>

</div>

<script>
let selectedAuction = null;
let timerInterval = null;

function selectAuction(card) {
    selectedAuction = {
        id: card.dataset.id,
        icon: card.dataset.icon,
        farm: card.dataset.farm,
        location: card.dataset.location,
        description: card.dataset.description,
        crop: card.dataset.crop,
        price: parseFloat(card.dataset.price),
        created: card.dataset.created
    };

    const bidPanel = document.getElementById("bidPanel");

    bidPanel.innerHTML = `
        <div class="selected-icon">${selectedAuction.icon}</div>

        <div class="selected-title">${selectedAuction.crop}</div>

        <div class="selected-info">${selectedAuction.description}</div>

        <div class="selected-info">
            Farmer: ${selectedAuction.farm}
        </div>

        <div class="selected-info">
            📍 ${selectedAuction.location}
        </div>

        <div class="selected-price">
            R${selectedAuction.price.toFixed(2)}
        </div>

        <div class="time-box">
            <div class="time-label">TIME REMAINING TO BID</div>
            <div class="time-value" id="timeRemaining">Loading...</div>
        </div>

        <input 
            type="number" 
            class="bid-input" 
            id="bidAmount"
            min="${selectedAuction.price + 1}"
            placeholder="Enter your bid amount"
        >

        <button class="bid-btn" onclick="placeBid()">
            Place Bid
        </button>

        <form method="POST" onsubmit="return confirm('Buy this item now? It will be removed from listings.');">
            <input type="hidden" name="harvest_id" value="${selectedAuction.id}">
            <button type="submit" name="buy_now" class="buy-btn">
                Buy Now
            </button>
        </form>
    `;

    startTimer(selectedAuction.created);
}

function startTimer(createdAt) {
    if (timerInterval) {
        clearInterval(timerInterval);
    }

    const createdTime = new Date(createdAt).getTime();

    const endTime = createdTime + (24 * 60 * 60 * 1000);

    timerInterval = setInterval(function () {
        const now = new Date().getTime();
        const distance = endTime - now;

        const timeBox = document.getElementById("timeRemaining");

        if (!timeBox) return;

        if (distance <= 0) {
            clearInterval(timerInterval);
            timeBox.innerHTML = "Auction ended";
            return;
        }

        const hours = Math.floor(distance / (1000 * 60 * 60));
        const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
        const seconds = Math.floor((distance % (1000 * 60)) / 1000);

        timeBox.innerHTML = `${hours}h ${minutes}m ${seconds}s`;
    }, 1000);
}

function placeBid() {
    const bidAmount = document.getElementById("bidAmount").value;

    if (!bidAmount) {
        alert("Please enter your bid amount.");
        return;
    }

    if (parseFloat(bidAmount) <= selectedAuction.price) {
        alert("Your bid must be higher than the current price.");
        return;
    }

    alert("Bid placed: R" + parseFloat(bidAmount).toFixed(2));
}
</script>

</body>
</html>