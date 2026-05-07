// ── CONFIG ──────────────────────────────────────────────────────────────────
const API = 'api';
// Demo user IDs — in a real app these come from session
const CONSUMER_ID = 4; // Thandi Mokoena
const FARMER_ID   = 1; // Mama Nkosi / Nkosi Family Farm

// ── STATE ───────────────────────────────────────────────────────────────────
let listings       = [];
let selectedId     = null;
let myBidAmount    = 0;
let isWinning      = false;
let pollInterval   = null;
let farmerSection  = 'dashboard';
let selProduce     = 0;
let selWindow      = 30;
let farmerData     = null;

const PRODUCE = [
  {e:'🍅',n:'Tomatoes'},{e:'🥬',n:'Spinach'},{e:'🌽',n:'Corn'},{e:'🎃',n:'Butternut'},
  {e:'🥕',n:'Carrots'},{e:'🥦',n:'Broccoli'},{e:'🧅',n:'Onions'},{e:'🥔',n:'Potatoes'},
  {e:'🫑',n:'Peppers'},{e:'🍆',n:'Brinjal'},{e:'🌿',n:'Herbs'},{e:'🍋',n:'Lemons'},
  {e:'🥭',n:'Mangoes'},{e:'🍓',n:'Berries'},{e:'🫛',n:'Peas'},{e:'🌰',n:'Nuts'},
  {e:'🍠',n:'Sweet pot'},{e:'🥒',n:'Cucumber'},{e:'🧄',n:'Garlic'},{e:'🌶️',n:'Chilli'}
];

// Card background colours per produce
const CARD_COLORS = {
  '🍅':'#fff0ed','🥬':'#ecfdf5','🌽':'#fefce8','🎃':'#fff7ed',
  '🥕':'#fff7ed','🥦':'#f0fdf4','🧅':'#fafaf9','🥔':'#fef9c3',
  '🫑':'#ecfdf5','🍆':'#faf5ff','🌿':'#f0fdf4','🍋':'#fefce8',
  '🥭':'#fff7ed','🍓':'#fff1f2','🫛':'#f0fdf4','🌰':'#fef3c7',
  '🍠':'#fff7ed','🥒':'#ecfdf5','🧄':'#fafaf9','🌶️':'#fff1f2'
};

// ── UTILS ────────────────────────────────────────────────────────────────────
function fmtTime(s) {
  if (s <= 0) return '00:00';
  const m = Math.floor(s / 60), sec = s % 60;
  return (m < 10 ? '0' : '') + m + ':' + (sec < 10 ? '0' : '') + sec;
}

function fmtCurrency(n) {
  return 'R' + Number.parseFloat(n).toFixed(2).replace(/\.00$/, '');
}

function statusBadgeClass(status) {
  if (status === 'sold') return 'badge-sold';
  if (status === 'live') return 'badge-live';
  return 'badge-exp';
}

function timeSince(dateStr) {
  const diff = Math.round((Date.now() - new Date(dateStr).getTime()) / 1000);
  if (diff < 60) return diff + 's ago';
  if (diff < 3600) return Math.floor(diff / 60) + 'm ago';
  return Math.floor(diff / 3600) + 'h ago';
}

function showToast(msg, type = 'success') {
  const t = document.getElementById('toast');
  t.textContent = msg;
  t.className = 'toast show' + (type === 'error' ? ' error' : '');
  clearTimeout(t._timer);
  t._timer = setTimeout(() => t.classList.remove('show'), 3500);
}

async function apiFetch(url, opts = {}) {
  try {
    const res = await fetch(url, { headers: { 'Content-Type': 'application/json' }, ...opts });
    if (!res.ok) {
      console.error('API fetch failed', url, res.status, res.statusText);
      return { error: `Request failed with status ${res.status}` };
    }

    const data = await res.json().catch(err => {
      console.error('Invalid JSON response from', url, err);
      return { error: 'Invalid JSON response' };
    });

    return data;
  } catch (err) {
    console.error('Fetch error', url, err);
    return { error: err.message || 'Network error' };
  }
}

// ── SCREEN SWITCH ────────────────────────────────────────────────────────────
function switchScreen(id) {
  document.querySelectorAll('.screen').forEach(s => s.classList.remove('active'));
  document.getElementById('screen-' + id).classList.add('active');
  document.querySelectorAll('.nav-tab').forEach((t, i) => {
    t.classList.toggle('active', ['home', 'consumer', 'farmer'][i] === id);
  });
  clearInterval(pollInterval);
  if (id === 'consumer') {
    loadListings();
    pollInterval = setInterval(loadListings, 4000);
    startCountdowns();
  }
  if (id === 'farmer') {
    loadFarmerDashboard();
  }
}

// ── CONSUMER ─────────────────────────────────────────────────────────────────
async function loadListings() {
  const grid = document.getElementById('auctionGrid');
  grid.innerHTML = '<div class="loading"><div class="spinner"></div> Loading live auctions...</div>';
  const data = await apiFetch(`${API}/listings.php`);
  if (!data.success) {
    grid.innerHTML = `<div class="empty-state"><div class="empty-icon">⚠️</div><div>${data.error || 'Unable to load auctions'}</div></div>`;
    return;
  }

  const prevBids = {};
  listings.forEach(l => { prevBids[l.id] = l.current_bid; });

  listings = data.listings;

  // Update live count badge
  document.getElementById('liveCount').textContent = listings.length + ' live';

  renderAuctionGrid(prevBids);

  // If selected listing updated, refresh bid panel
  if (selectedId) {
    const updated = listings.find(l => l.id == selectedId);
    if (updated) {
      const wasMyBid = isWinning;
      if (!wasMyBid) {
        myBidAmount = updated.current_bid;
      }
      updateBidPanelLive(updated);
    }
  }
}

function renderAuctionGrid(prevBids = {}) {
  const grid = document.getElementById('auctionGrid');
  if (!listings.length) {
    grid.innerHTML = '<div class="empty-state"><div class="empty-icon">🌱</div><div>No live auctions right now. Check back soon!</div></div>';
    return;
  }

  grid.innerHTML = listings.map(l => {
    const secs = l.seconds_left;
    const isUrgent = secs < 300;
    const saving = Math.round(100 - (l.current_bid / l.retail_value * 100));
    const bg = CARD_COLORS[l.emoji] || '#f5f5f4';
    const isSelected = l.id == selectedId;
    const bumped = prevBids[l.id] && prevBids[l.id] < l.current_bid;
    return `
      <div class="auction-card${isSelected ? ' selected' : ''}" id="acard-${l.id}" onclick="selectAuction(${l.id})">
        <div class="card-img" style="background:${bg}">
          <span>${l.emoji}</span>
          <div class="card-img-overlay"></div>
          <div class="card-timer${isUrgent ? ' urgent' : ''}" id="ctimer-${l.id}">${fmtTime(secs)}</div>
        </div>
        <div class="card-body">
          <div class="card-farm">${l.farmer_name}</div>
          <div class="card-desc">📍 ${l.farmer_location} · ${l.description}</div>
          <div class="card-tags">
            <span class="tag tag-g">no middleman</span>
            ${l.weight_kg ? `<span class="tag tag-a">${l.weight_kg}kg</span>` : ''}
            <span class="tag tag-r">${l.bid_count} bidding</span>
          </div>
          <div class="card-bottom">
            <div>
              <div class="card-bid-lbl">Current bid</div>
              <div class="card-bid-amt${bumped ? ' bump' : ''}" id="cbid-${l.id}">${fmtCurrency(l.current_bid)}</div>
            </div>
            <div class="card-meta">
              <div class="card-retail">${fmtCurrency(l.retail_value)}</div>
              <div style="color:var(--green);font-weight:500;">Save ${saving}%</div>
            </div>
          </div>
        </div>
      </div>
    `;
  }).join('');
}

function startCountdowns() {
  setInterval(() => {
    listings.forEach(l => {
      if (l.seconds_left > 0) {
        l.seconds_left--;
        const el = document.getElementById('ctimer-' + l.id);
        if (el) {
          el.textContent = fmtTime(l.seconds_left);
          el.className = 'card-timer' + (l.seconds_left < 300 ? ' urgent' : '');
        }
      }
    });
    if (selectedId) {
      const l = listings.find(x => x.id == selectedId);
      if (l) {
        const tv = document.getElementById('panel-timer');
        const tp = document.getElementById('panel-progress');
        const maxSecs = l.seconds_left > 1800 ? 3600 : 1800;
        if (tv) tv.textContent = fmtTime(l.seconds_left);
        if (tp) tp.style.width = Math.min(100, Math.round(100 - (l.seconds_left / maxSecs * 100))) + '%';
      }
    }
  }, 1000);
}

async function selectAuction(id) {
  selectedId = id;
  isWinning = false;
  const listing = listings.find(l => l.id == id);
  if (!listing) return;

  myBidAmount = listing.current_bid;

  document.querySelectorAll('.auction-card').forEach(c => c.classList.remove('selected'));
  const card = document.getElementById('acard-' + id);
  if (card) card.classList.add('selected');

  document.getElementById('bidEmpty').style.display = 'none';
  document.getElementById('bidContent').style.display = 'flex';

  await renderBidPanel(listing);
}

async function renderBidPanel(listing) {
  const saving = Math.round(100 - (myBidAmount / listing.retail_value * 100));
  const maxSecs = listing.seconds_left > 1800 ? 3600 : 1800;
  const pct = Math.min(100, Math.round(100 - (listing.seconds_left / maxSecs * 100)));

  // Load bid history
  const bidData = await apiFetch(`${API}/get_bids.php?listing_id=${listing.id}`);
  const bids = bidData.bids || [];

  document.getElementById('bidContent').innerHTML = `
    <div class="bid-item-header">
      <div class="bid-item-emoji">${listing.emoji}</div>
      <div>
        <div class="bid-item-title">${listing.farmer_name}</div>
        <div class="bid-item-sub">📍 ${listing.farmer_location} · ${listing.description}</div>
      </div>
    </div>

    <div class="bid-timer-block">
      <div class="bid-timer-row">
        <div>
          <div class="bid-timer-lbl">Time remaining</div>
          <div class="bid-timer-val" id="panel-timer">${fmtTime(listing.seconds_left)}</div>
        </div>
        <div style="text-align:right">
          <div class="bid-timer-lbl">Bids</div>
          <div class="bid-count-val" id="panel-bidcount">${listing.bid_count}</div>
        </div>
      </div>
      <div class="bid-progress"><div class="bid-progress-fill" id="panel-progress" style="width:${pct}%"></div></div>
    </div>

    <div class="bid-price-section">
      <div class="bid-current-lbl">Your bid</div>
      <div class="bid-current" id="panel-bid">${fmtCurrency(myBidAmount)}</div>
      <div style="font-size:12px;color:var(--ink4);margin-top:2px;">Retail: <span style="text-decoration:line-through">${fmtCurrency(listing.retail_value)}</span></div>
      <div class="bid-saving" id="panel-saving">You save ${saving}% vs supermarket</div>
    </div>

    <div class="bid-inc-row">
      <button class="bid-inc-btn" onclick="incBid(5)">+R5</button>
      <button class="bid-inc-btn" onclick="incBid(10)">+R10</button>
      <button class="bid-inc-btn" onclick="incBid(20)">+R20</button>
    </div>

    <button class="bid-main-btn${isWinning ? ' winning' : ''}" id="bidMainBtn" onclick="openBidModal()">
      ${isWinning ? '🏆 You\'re winning!' : 'Place Bid — ' + fmtCurrency(myBidAmount)}
    </button>

    <div>
      <div class="bid-feed-title">Live bid feed</div>
      <div class="bid-feed" id="bidFeed">
        ${bids.map(b => `
          <div class="bfr">
            <div class="bfr-av" style="background:${b.avatar_color};color:${b.avatar_text_color}">${b.avatar_initials}</div>
            <div style="flex:1">
              <div class="bfr-name">${b.bidder_name}</div>
              <div class="bfr-loc">${b.bidder_location} · ${timeSince(b.created_at)}</div>
            </div>
            <div class="bfr-amt">${fmtCurrency(b.amount)}</div>
          </div>
        `).join('') || '<div style="padding:12px;font-size:13px;color:var(--ink4);text-align:center;">No bids yet — be the first!</div>'}
      </div>
    </div>

    ${listing.pickup_notes ? `
      <div style="background:var(--amber-faint);border-radius:var(--radius-sm);padding:10px 12px;font-size:12px;color:#633806;">
        📦 <strong>Pickup:</strong> ${listing.pickup_notes}
      </div>
    ` : ''}
  `;
}

function updateBidPanelLive(listing) {
  const bc = document.getElementById('panel-bidcount');
  const bidEl = document.getElementById('cbid-' + listing.id);
  if (bc) bc.textContent = listing.bid_count;
  if (bidEl) bidEl.textContent = fmtCurrency(listing.current_bid);

  // Refresh feed silently
  apiFetch(`${API}/get_bids.php?listing_id=${listing.id}`).then(data => {
    const feed = document.getElementById('bidFeed');
    if (!feed || !data.bids) return;
    const bids = data.bids;
    if (bids.length && fmtCurrency(bids[0].amount) !== feed.querySelector('.bfr-amt')?.textContent) {
      const row = document.createElement('div');
      row.className = 'bfr flash';
      const b = bids[0];
      row.innerHTML = `
        <div class="bfr-av" style="background:${b.avatar_color};color:${b.avatar_text_color}">${b.avatar_initials}</div>
        <div style="flex:1"><div class="bfr-name">${b.bidder_name}</div><div class="bfr-loc">${b.bidder_location} · just now</div></div>
        <div class="bfr-amt">${fmtCurrency(b.amount)}</div>
      `;
      feed.insertBefore(row, feed.firstChild);
      if (feed.children.length > 6) feed.lastChild?.remove();
      if (!isWinning) showToast(`${b.bidder_name} just bid ${fmtCurrency(b.amount)} on ${listing.emoji} ${listing.farmer_name}`);
    }
  });
}

function incBid(amount) {
  myBidAmount = Number.parseFloat((myBidAmount + amount).toFixed(2));
  const el = document.getElementById('panel-bid');
  const btn = document.getElementById('bidMainBtn');
  const sav = document.getElementById('panel-saving');
  const listing = listings.find(l => l.id == selectedId);
  if (!listing) return;
  if (el) el.textContent = fmtCurrency(myBidAmount);
  if (btn && !isWinning) btn.textContent = 'Place Bid — ' + fmtCurrency(myBidAmount);
  if (sav) {
    const saving = Math.round(100 - (myBidAmount / listing.retail_value * 100));
    sav.textContent = `You save ${saving}% vs supermarket`;
  }
}

function openBidModal() {
  if (!selectedId) return;
  const listing = listings.find(l => l.id == selectedId);
  if (!listing) return;
  const saving = Math.round(100 - (myBidAmount / listing.retail_value * 100));
  document.getElementById('modalTitle').textContent = 'Confirm your bid';
  document.getElementById('modalSub').textContent = `${listing.emoji} ${listing.description} from ${listing.farmer_name}, ${listing.farmer_location}`;
  document.getElementById('modalAmount').textContent = fmtCurrency(myBidAmount);
  document.getElementById('modalDetail').textContent = `You save R${(listing.retail_value - myBidAmount).toFixed(2)} (${saving}%) vs retail price of ${fmtCurrency(listing.retail_value)}`;
  document.getElementById('modalError').textContent = '';
  document.getElementById('modalOverlay').classList.add('open');
}

function closeModal() {
  document.getElementById('modalOverlay').classList.remove('open');
}

async function confirmBid() {
  const listing = listings.find(l => l.id == selectedId);
  if (!listing) return;

  const btn = document.getElementById('modalConfirm');
  btn.textContent = 'Placing...';
  btn.disabled = true;

  const data = await apiFetch(`${API}/bid.php`, {
    method: 'POST',
    body: JSON.stringify({ listing_id: selectedId, bidder_id: CONSUMER_ID, amount: myBidAmount })
  });

  btn.textContent = 'Confirm Bid';
  btn.disabled = false;

  if (data.error) {
    document.getElementById('modalError').textContent = '⚠️ ' + data.error;
    return;
  }

  closeModal();
  isWinning = true;

  listing.current_bid = data.new_bid;
  listing.bid_count = data.bid_count;

  const bidBtn = document.getElementById('bidMainBtn');
  if (bidBtn) { bidBtn.textContent = "🏆 You're winning!"; bidBtn.classList.add('winning'); }

  const cbid = document.getElementById('cbid-' + selectedId);
  if (cbid) cbid.textContent = fmtCurrency(data.new_bid);

  // Add your bid to feed
  const feed = document.getElementById('bidFeed');
  if (feed) {
    const row = document.createElement('div');
    row.className = 'bfr flash';
    row.innerHTML = `
      <div class="bfr-av" style="background:var(--green-dark);color:#fff">TM</div>
      <div style="flex:1"><div class="bfr-name">You 🏆</div><div class="bfr-loc">Tembisa · just now</div></div>
      <div class="bfr-amt" style="color:var(--green-dark)">${fmtCurrency(data.new_bid)}</div>
    `;
    feed.insertBefore(row, feed.firstChild);
    if (feed.children.length > 6) feed.lastChild?.remove();
  }

  const bc = document.getElementById('panel-bidcount');
  if (bc) bc.textContent = data.bid_count;

  showToast(`🏆 You're winning! ${fmtCurrency(data.new_bid)} on ${listing.emoji} ${listing.farmer_name}`, 'success');
}

// ── FARMER ────────────────────────────────────────────────────────────────────
async function loadFarmerDashboard() {
  const main = document.getElementById('farmerMain');
  main.innerHTML = '<div class="loading"><div class="spinner"></div> Loading dashboard...</div>';
  const data = await apiFetch(`${API}/farmer_stats.php?farmer_id=${FARMER_ID}`);
  if (!data.success) {
    main.innerHTML = `<div class="loading"><div class="spinner"></div> ${data.error || 'Unable to load farmer dashboard'}</div>`;
    return;
  }
  farmerData = data;
  renderFarmerSection();

  const earningsEl = document.getElementById('sidebarEarnings');
  if (earningsEl) {
    earningsEl.textContent = fmtCurrency(farmerData.week_earnings);
  }
  const activeEl = document.getElementById('activeBadge');
  if (activeEl) {
    activeEl.textContent = farmerData.active_count;
  }
}

function farmerTab(tab, el) {
  farmerSection = tab;
  document.querySelectorAll('.fsb-item').forEach(i => i.classList.remove('active'));
  if (el) el.classList.add('active');
  renderFarmerSection();
}

function renderFarmerSection() {
  const main = document.getElementById('farmerMain');
  main.innerHTML = '<div class="loading"><div class="spinner"></div> Loading...</div>';
  setTimeout(() => {
    if (farmerSection === 'dashboard') main.innerHTML = buildDashboard();
    if (farmerSection === 'post')      main.innerHTML = buildPost();
    if (farmerSection === 'active')    main.innerHTML = buildActive();
    if (farmerSection === 'history')   main.innerHTML = buildHistory();
    if (farmerSection === 'post') attachProduceEvents();
  }, 80);
}

function buildDashboard() {
  if (!farmerData) return '<div class="loading"><div class="spinner"></div></div>';
  const d = farmerData;
  const maxEarned = Math.max(...(d.chart.map(c => Number.parseFloat(c.earned)) || [1]));
  return `
    <div class="section-title">Dashboard</div>
    <div class="section-sub">Good morning, Nkosi Family Farm 👋</div>
    <div class="metric-row">
      <div class="metric-card"><div class="mc-icon">💰</div><div class="mc-label">This week</div><div class="mc-val">${fmtCurrency(d.week_earnings)}</div><div class="mc-sub">↑ vs last week</div></div>
      <div class="metric-card"><div class="mc-icon">🔨</div><div class="mc-label">Total sold</div><div class="mc-val">${d.total_sold}</div><div class="mc-sub">All-time auctions</div></div>
      <div class="metric-card"><div class="mc-icon">🔥</div><div class="mc-label">Live now</div><div class="mc-val">${d.active_count}</div><div class="mc-sub">Active auctions</div></div>
      <div class="metric-card"><div class="mc-icon">⭐</div><div class="mc-label">Rating</div><div class="mc-val">4.9</div><div class="mc-sub">From 47 reviews</div></div>
    </div>
    <div class="two-col">
      <div class="card-panel">
        <div class="cp-title">Earnings — last 7 days</div>
        ${d.chart.length ? d.chart.map(c => `
          <div class="chart-bar-row">
            <div class="chart-lbl">${new Date(c.day).toLocaleDateString('en-ZA',{weekday:'short'})}</div>
            <div class="chart-bar-bg"><div class="chart-bar-fill" style="width:${Math.round(c.earned/maxEarned*100)}%"></div></div>
            <div class="chart-val">${fmtCurrency(c.earned)}</div>
          </div>
        `).join('') : '<div style="color:var(--ink4);font-size:13px;">No sales this week yet — post your first harvest!</div>'}
      </div>
      <div style="display:flex;flex-direction:column;gap:16px;">
        <div class="card-panel">
          <div class="cp-title">Quick post</div>
          <p style="font-size:13px;color:var(--ink3);margin-bottom:12px;">Harvested something? Post it live in 30 seconds.</p>
          <button class="go-live-btn" style="margin-top:0;" onclick="farmerTab('post', document.querySelectorAll('.fsb-item')[1])">Post a Harvest →</button>
        </div>
        <div class="card-panel">
          <div class="cp-title">Active listings</div>
          ${d.active_listings.length ? d.active_listings.map(l => `
            <div class="listing-row">
              <div class="lr-emoji">${l.emoji}</div>
              <div class="lr-info"><div class="lr-title">${l.title}</div><div class="lr-bids">${l.bid_count} bids</div></div>
              <div class="lr-right"><div class="lr-timer${l.seconds_left < 300 ? ' urgent' : ''}">${fmtTime(l.seconds_left)}</div><div class="lr-price">${fmtCurrency(l.current_bid)}</div></div>
            </div>
          `).join('') : '<div style="color:var(--ink4);font-size:13px;padding:8px 0;">No active listings.</div>'}
        </div>
      </div>
    </div>
  `;
}

function buildPost() {
  return `
    <div style="max-width:600px;">
      <div class="section-title">Post a Harvest</div>
      <div class="section-sub">Goes live instantly — nearby buyers are notified immediately</div>
      <div class="card-panel">
        <div class="form-group">
          <label class="form-label">What are you selling today?</label>
          <div class="produce-grid" id="produceGrid">
            ${PRODUCE.map((p, i) => `
              <div class="produce-item${i === selProduce ? ' sel' : ''}" data-idx="${i}">
                <div class="produce-emoji">${p.e}</div>
                <div class="produce-name">${p.n}</div>
              </div>
            `).join('')}
          </div>
        </div>
        <div class="form-group">
          <label class="form-label">Harvest description</label>
          <input class="form-input" type="text" id="fTitle" placeholder="e.g. 12kg fresh tomatoes, picked this morning">
        </div>
        <div class="form-group">
          <label class="form-label">Extra details (optional)</label>
          <input class="form-input" type="text" id="fDesc" placeholder="e.g. No pesticides, certified organic, great for sauce">
        </div>
        <div class="form-group">
          <label class="form-label">Weight (kg)</label>
          <input class="form-input" type="number" id="fWeight" placeholder="e.g. 12" min="0.5" max="500" style="width:160px;">
        </div>
        <div class="form-group">
          <label class="form-label">Reserve price (minimum you'll accept)</label>
          <div class="range-row">
            <input class="form-range" type="range" min="10" max="300" step="5" value="45" id="fReserve" oninput="document.getElementById('fReserveVal').textContent='R'+this.value">
            <div class="range-val" id="fReserveVal">R45</div>
          </div>
        </div>
        <div class="form-group">
          <label class="form-label">Estimated retail value</label>
          <div class="range-row">
            <input class="form-range" type="range" min="20" max="600" step="10" value="120" id="fRetail" oninput="document.getElementById('fRetailVal').textContent='R'+this.value">
            <div class="range-val" id="fRetailVal">R120</div>
          </div>
        </div>
        <div class="form-group">
          <label class="form-label">Auction window</label>
          <div class="window-row">
            ${[['15','15 min'],['30','30 min'],['60','1 hour'],['120','2 hours']].map(([v,lbl]) => `
              <button class="window-btn${v == selWindow ? ' sel' : ''}" onclick="setWindow(${v},this)">${lbl}</button>
            `).join('')}
          </div>
        </div>
        <div class="form-group">
          <label class="form-label">Pickup / delivery notes</label>
          <input class="form-input" type="text" id="fPickup" placeholder="e.g. Farm gate pickup, or Bronkhorstspruit Mall Pargo point">
        </div>
        <button class="go-live-btn" onclick="submitListing()">🔥 Go Live Now</button>
        <div id="postMsg" style="margin-top:10px;font-size:13px;min-height:18px;"></div>
      </div>
    </div>
  `;
}

function attachProduceEvents() {
  document.querySelectorAll('.produce-item').forEach(el => {
    el.addEventListener('click', () => {
      selProduce = Number.parseInt(el.dataset.idx);
      document.querySelectorAll('.produce-item').forEach(p => p.classList.remove('sel'));
      el.classList.add('sel');
    });
  });
}

function setWindow(val, el) {
  selWindow = val;
  document.querySelectorAll('.window-btn').forEach(b => b.classList.remove('sel'));
  el.classList.add('sel');
}

async function submitListing() {
  const title = document.getElementById('fTitle').value.trim();
  if (!title) { showToast('Please enter a harvest description', 'error'); return; }

  const payload = {
    farmer_id: FARMER_ID,
    emoji: PRODUCE[selProduce].e,
    title,
    description: document.getElementById('fDesc').value.trim(),
    weight_kg: Number.parseFloat(document.getElementById('fWeight').value) || 0,
    reserve: Number.parseFloat(document.getElementById('fReserve').value),
    retail: Number.parseFloat(document.getElementById('fRetail').value),
    minutes: selWindow,
    pickup: document.getElementById('fPickup').value.trim()
  };

  const msg = document.getElementById('postMsg');
  msg.textContent = '⏳ Posting...';
  msg.style.color = 'var(--ink3)';

  const data = await apiFetch(`${API}/post_listing.php`, { method: 'POST', body: JSON.stringify(payload) });

  if (data.error) {
    msg.textContent = '⚠️ ' + data.error;
    msg.style.color = 'var(--red)';
    return;
  }

  msg.textContent = '✅ ' + data.message;
  msg.style.color = 'var(--green)';
  showToast('🔥 Your harvest is live! Buyers are being notified.');

  setTimeout(() => {
    loadFarmerDashboard();
    farmerTab('active', document.querySelectorAll('.fsb-item')[2]);
  }, 1500);
}

function buildActive() {
  if (!farmerData) return '';
  const listings = farmerData.active_listings;
  return `
    <div style="max-width:680px;">
      <div class="section-title">Active Listings</div>
      <div class="section-sub">${listings.length} auction${listings.length === 1 ? '' : 's'} running live</div>
      <div class="card-panel">
        ${listings.length ? listings.map(l => `
          <div class="listing-row">
            <div class="lr-emoji">${l.emoji}</div>
            <div class="lr-info">
              <div class="lr-title">${l.title}</div>
              <div class="lr-sub">Listing #${l.id}</div>
              <div class="lr-bids">${l.bid_count} active bid${l.bid_count === 1 ? '' : 's'}</div>
            </div>
            <div class="lr-right">
              <div class="lr-timer${l.seconds_left < 300 ? ' urgent' : ''}">${fmtTime(l.seconds_left)}</div>
              <div class="lr-price">${fmtCurrency(l.current_bid)}</div>
              <div style="font-size:10px;color:var(--green);">current bid</div>
            </div>
          </div>
        `).join('') : '<div class="empty-state"><div class="empty-icon">🌱</div><div>No active listings. <a href="#" onclick="farmerTab(\'post\',null)" style="color:var(--green);">Post a harvest →</a></div></div>'}
        ${listings.length ? `
          <div style="margin-top:10px;padding-top:12px;border-top:1px solid var(--border);display:flex;justify-content:space-between;">
            <div style="font-size:13px;color:var(--ink3);">Total current value</div>
            <div style="font-family:'Syne',sans-serif;font-size:20px;font-weight:800;color:var(--green-dark);">${fmtCurrency(listings.reduce((s,l)=>s+l.current_bid,0))}</div>
          </div>
        ` : ''}
      </div>
    </div>
  `;
}

function buildHistory() {
  if (!farmerData) return '';
  const rows = farmerData.history;
  return `
    <div style="max-width:680px;">
      <div class="section-title">Sales History</div>
      <div class="section-sub">All your auction activity — 0% middleman cut</div>
      <div class="card-panel" style="padding:0;overflow:hidden;">
        ${rows.length ? `
          <table class="hist-table">
            <thead><tr><th>Produce</th><th>Final price</th><th>Bids</th><th>Date</th><th>Status</th></tr></thead>
            <tbody>
              ${rows.map(r => {
                const statusClass = statusBadgeClass(r.status);
                return `
                <tr>
                  <td>${r.emoji} ${r.title}</td>
                  <td style="font-family:'Syne',sans-serif;font-weight:700;color:var(--green-dark);">${fmtCurrency(r.current_bid)}</td>
                  <td>${r.bid_count}</td>
                  <td>${new Date(r.ends_at).toLocaleDateString('en-ZA')}</td>
                  <td><span class="${statusClass}">${r.status}</span></td>
                </tr>
              `;
              }).join('')}
            </tbody>
          </table>
        ` : '<div class="empty-state" style="padding:30px;"><div class="empty-icon">📋</div><div>No sales history yet.</div></div>'}
      </div>
    </div>
  `;
}

// ── INIT ──────────────────────────────────────────────────────────────────────
function toggleFilter(el) {
  document.querySelectorAll('.filter-chip').forEach(c => c.classList.remove('on'));
  el.classList.add('on');
}

document.addEventListener('DOMContentLoaded', () => {
  // Load the initial auction list and start countdown timers
  loadListings();
  startCountdowns();
});
