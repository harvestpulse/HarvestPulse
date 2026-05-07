<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>HarvestPulse — Live Farm Auctions</title>
  <link rel="stylesheet" href="css/style.css">
</head>
<body>

<!-- ── NAV ────────────────────────────────────────────────────────────────── -->
<nav class="nav">
  <div class="logo">
    <span class="logo-dot"></span>HarvestPulse
  </div>
  <div class="nav-tabs">
    <button class="nav-tab active" onclick="switchScreen('home')">Home</button>
    <button class="nav-tab" onclick="switchScreen('consumer')">Browse Auctions</button>
    <button class="nav-tab" onclick="switchScreen('farmer')">Farmer Dashboard</button>
  </div>
  <div class="nav-right">
    <div class="live-badge">
      <span class="live-dot"></span>
      <span id="liveCount">— live</span>
    </div>
    <div class="user-chip">
      <div class="user-av" style="background:#0f4f2e;color:#fff">TM</div>
      Thandi M.
    </div>
  </div>
</nav>

<!-- ── HOME ───────────────────────────────────────────────────────────────── -->
<div class="screen active" id="screen-home">
  <div class="home-wrap">
    <div class="home-card">
      <div class="home-emoji">🌱</div>
      <div class="home-title">Welcome to HarvestPulse</div>
      <div class="home-sub">
        South Africa's first live farm-to-buyer auction platform.
        Farmers post fresh harvests. Consumers bid in real time.
        No middlemen. Everyone wins.
      </div>
      <div class="home-btns">
        <button class="btn-primary" onclick="switchScreen('consumer')">Browse Live Auctions 🔥</button>
        <button class="btn-secondary" onclick="switchScreen('farmer')">I'm a Farmer — Post a Harvest</button>
      </div>
      <div class="home-stats">
        <div><div class="hs-val">R50B</div><div class="hs-lbl">SA fresh produce market</div></div>
        <div><div class="hs-val">55%</div><div class="hs-lbl">Below retail prices</div></div>
        <div><div class="hs-val">2–3×</div><div class="hs-lbl">More for farmers</div></div>
      </div>
    </div>
  </div>
</div>

<!-- ── CONSUMER ────────────────────────────────────────────────────────────── -->
<div class="screen" id="screen-consumer">
  <div class="consumer-layout">

    <!-- Left: auction grid -->
    <div class="auctions-panel">
      <div class="panel-header">
        <div>
          <div class="panel-title">Live Auctions</div>
          <div class="panel-sub">Gauteng region · Prices update live from the database</div>
        </div>
      </div>
      <div class="filter-row">
        <button class="filter-chip on" onclick="toggleFilter(this)">All</button>
        <button class="filter-chip" onclick="toggleFilter(this)">Vegetables</button>
        <button class="filter-chip" onclick="toggleFilter(this)">Fruit</button>
        <button class="filter-chip" onclick="toggleFilter(this)">Organic</button>
        <button class="filter-chip" onclick="toggleFilter(this)">Ending soon</button>
      </div>
      <div id="auctionGrid" class="auction-grid">
        <div class="loading"><div class="spinner"></div> Loading live auctions...</div>
      </div>
    </div>

    <!-- Right: bid panel -->
    <div class="bid-panel">
      <div class="bid-empty" id="bidEmpty">
        <div class="bid-empty-icon">👆</div>
        <div style="font-size:14px;">Select an auction to start bidding in real time</div>
      </div>
      <div class="bid-content" id="bidContent" style="display:none;flex-direction:column;gap:14px;"></div>
    </div>

  </div>
</div>

<!-- ── FARMER ──────────────────────────────────────────────────────────────── -->
<div class="screen" id="screen-farmer">
  <div class="farmer-layout">

    <!-- Sidebar -->
    <div class="farmer-sidebar">
      <div style="padding:10px;margin-bottom:6px;">
        <div style="font-family:'Syne',sans-serif;font-size:15px;font-weight:700;color:#fff;">Nkosi Family Farm</div>
        <div style="font-size:11px;color:rgba(255,255,255,.45);margin-top:2px;">Bronkhorstspruit · ✅ Verified</div>
      </div>
      <div class="fsb-section">Menu</div>
      <div class="fsb-item active" onclick="farmerTab('dashboard', this)">
        <span class="fsb-icon">📊</span> Dashboard
      </div>
      <div class="fsb-item" onclick="farmerTab('post', this)">
        <span class="fsb-icon">➕</span> Post a Harvest
      </div>
      <div class="fsb-item" onclick="farmerTab('active', this)">
        <span class="fsb-icon">🔥</span> Active Listings
        <span class="fsb-badge" id="activeBadge">—</span>
      </div>
      <div class="fsb-item" onclick="farmerTab('history', this)">
        <span class="fsb-icon">📋</span> Sales History
      </div>
      <div class="fsb-bottom">
        <div style="font-size:10px;color:rgba(255,255,255,.4);margin-bottom:3px;">This week's earnings</div>
        <div style="font-family:'Syne',sans-serif;font-size:26px;font-weight:800;color:#fff;" id="sidebarEarnings">—</div>
        <div style="font-size:11px;color:rgba(255,255,255,.4);margin-top:2px;">0% middleman cut</div>
      </div>
    </div>

    <!-- Main content -->
    <div id="farmerMain">
      <div class="loading"><div class="spinner"></div> Loading dashboard...</div>
    </div>

  </div>
</div>

<!-- ── BID CONFIRM MODAL ────────────────────────────────────────────────────── -->
<div class="modal-overlay" id="modalOverlay" onclick="if(event.target===this)closeModal()">
  <div class="modal">
    <div class="modal-title" id="modalTitle"></div>
    <div class="modal-sub" id="modalSub"></div>
    <div class="modal-amount" id="modalAmount"></div>
    <div class="modal-detail" id="modalDetail"></div>
    <div class="modal-btns">
      <button class="modal-cancel" onclick="closeModal()">Cancel</button>
      <button class="modal-confirm" id="modalConfirm" onclick="confirmBid()">Confirm Bid</button>
    </div>
    <div class="modal-error" id="modalError"></div>
  </div>
</div>

<!-- ── TOAST ────────────────────────────────────────────────────────────────── -->
<div class="toast" id="toast"></div>

<script src="js/app.js"></script>
<script>
  // Update sidebar earnings once farmer data loads
  const _origLoadFarmer = loadFarmerDashboard;
  loadFarmerDashboard = async function() {
    await _origLoadFarmer();
    if (farmerData) {
      document.getElementById('sidebarEarnings').textContent = fmtCurrency(farmerData.week_earnings);
      document.getElementById('activeBadge').textContent = farmerData.active_count;
    }
  };

  function toggleFilter(el) {
    document.querySelectorAll('.filter-chip').forEach(c => c.classList.remove('on'));
    el.classList.add('on');
  }
</script>

</body>
</html>
