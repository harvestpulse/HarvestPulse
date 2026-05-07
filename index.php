<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home</title>
    <link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Sans:ital,wght@0,300;0,400;0,500;1,300&display=swap"rel="stylesheet">
    <link href="collective_style.css" rel="stylesheet">
</head>

<nav class="nav">
  <div class="logo"><span class="logo-dot"></span>HarvestPulse</div>
  <div class="nav-tabs">
    <a class="nav-tab active" href = "index.php">Home</a>
    <a class="nav-tab" href = "browseAuctions.html">Browse Auctions</a>
    <a class="nav-tab" href = "dashboard.php" >Farmer Dashboard</a>
  </div>
  <div class="nav-right">
    <div class="live-badge"><span class="live-dot"></span><span id="liveCount">4 live</span></div>
    <div class="user-chip" onclick="switchScreen('consumer')"><div class="user-av">TM</div>Thandi M.</div>
  </div>
</nav>

<body>
<div class="screen active" id="screen-onboard">
  <div class="onboard-wrap">
    <div class="onboard-card">
      <div class="onboard-emoji">🌱</div>
      <div class="onboard-title">Welcome to HarvestPulse</div>
      <div class="onboard-sub">South Africa's first live farm-to-buyer auction platform. Farmers post fresh harvests. You bid in real time. Everyone wins.</div>
      <div class="onboard-btns">
        <a class="onboard-btn ob-primary" href = "browseAuctions.html">Browse Live Auctions 🔥</a>
        <a class="onboard-btn ob-secondary" href = "dashboard.php">I'm a Farmer — Post a Harvest</a>
      </div>
      <div style="margin-top:20px;padding-top:20px;border-top:1px solid var(--border);display:grid;grid-template-columns:repeat(3,1fr);gap:12px;">
        <div style="text-align:center;"><div style="font-family:'Syne',sans-serif;font-size:22px;font-weight:800;color:var(--green-dark);">R50B</div><div style="font-size:11px;color:var(--ink4);margin-top:2px;">Fresh produce market</div></div>
        <div style="text-align:center;"><div style="font-family:'Syne',sans-serif;font-size:22px;font-weight:800;color:var(--green-dark);">55%</div><div style="font-size:11px;color:var(--ink4);margin-top:2px;">Below retail prices</div></div>
        <div style="text-align:center;"><div style="font-family:'Syne',sans-serif;font-size:22px;font-weight:800;color:var(--green-dark);">2×</div><div style="font-size:11px;color:var(--ink4);margin-top:2px;">Farmer earnings</div></div>
      </div>
    </div>
  </div>
</div>
</body>

</html>