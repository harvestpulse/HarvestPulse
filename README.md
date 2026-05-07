HarvestPulse

Farm-fresh produce, auctioned live. Farmers earn more. You pay less. Delivered today.

HarvestPulse is a live farm-to-buyer auction platform built for South Africa's agricultural sector. It eliminates the middleman by connecting smallholder farmers directly to consumers, spaza shops, and stokvels through real-time bidding, giving farmers full control over how they price and sell their produce.

The Problem
South Africa's 2.5 million smallholder farmers earn as little as 25% of the final retail price of their produce. Industrial middlemen dominate the supply chain, dictating prices and taking up to 60% of the value. On the other side, township and urban consumers pay 2-4× the farm-gate price for the same food. No existing platform gives farmers the power to directly value, showcase, and sell their produce to buyers in real time.

The Solution
Farmers post a freshly harvested listing in under 30 seconds, a photo, description, weight, and minimum reserve price. A live countdown auction begins. Nearby consumers, spaza shops, and stokvels receive instant push notifications and compete in a real-time bidding feed. When the auction closes, the winner pays in-app and receives same-day delivery to a community drop point.
Result: Farmers earn 2–3× more. Buyers pay up to 55% less than retail. Zero middlemen.

Features

Live auction feed - real-time bidding with countdown timers and live bid updates
Farmer dashboard - one-tap harvest listing, earnings tracker, sales history
Consumer bidding - browse active auctions, place bids, get notified when outbid
Community drop points - last-mile delivery coordinated to spaza shops and taxi ranks
Role-based access - separate farmer and consumer interfaces
Push notifications - buyers alerted the moment a local farm goes live


Tech Stack
LayerTechnologyFrontendReact Native (mobile), React.js (web dashboard)BackendNode.js + ExpressDatabase & RealtimeFirebase Realtime Database + FirestoreAuthenticationFirebase Auth (phone OTP)NotificationsOneSignalPaymentsOzow / Peach PaymentsDeliveryPargo / Picup APILocationGoogle Maps APIDesignFigmaVersion ControlGitHub

Getting Started
Prerequisites

Node.js v18+
npm or yarn
Firebase project (free tier works)
OneSignal account (free tier)

Installation
bash# Clone the repository
git clone https://github.com/your-team/harvestpulse.git
cd harvestpulse

# Install dependencies
npm install

# Set up environment variables
cp .env.example .env
# Fill in your Firebase, OneSignal, Google Maps, and Ozow keys

# Start development server
npm run dev
Environment Variables
envFIREBASE_API_KEY=your_key
FIREBASE_AUTH_DOMAIN=your_domain
FIREBASE_DATABASE_URL=your_url
FIREBASE_PROJECT_ID=your_id
ONESIGNAL_APP_ID=your_id
GOOGLE_MAPS_API_KEY=your_key
OZOW_API_KEY=your_key
PARGO_API_KEY=your_key

Project Structure
harvestpulse/
├── index.php              ← The entire app (one page, 3 screens)
├── setup.sql              ← Run this FIRST in phpMyAdmin
├── includes/
│   └── db.php             ← Database connection config
├── api/
│   ├── listings.php       ← GET all live auctions
│   ├── bid.php            ← POST a bid (validates server-side)
│   ├── get_bids.php       ← GET bid history for a listing
│   ├── post_listing.php   ← POST a new farmer listing
│   └── farmer_stats.php   ← GET farmer dashboard data
├── css/
│   └── style.css
└── js/
    └── app.js

Security
HarvestPulse was built following a full Secure System Development Cycle (SSDC):

All bid logic runs server-side via Firebase Cloud Functions, never trusted from the client
JWT authentication with 1-hour expiry and role-based access (farmer vs consumer vs admin)
AES-256 encryption for delivery addresses at rest
Payment processing fully delegated to Ozow/Peach Payments, no card data touches HarvestPulse
POPIA compliant, user consent on registration, data deletion on request
Rate limiting on auction endpoints, max 10 bids/minute per user
All API keys stored in Cloud Function environment variables, never in client-side code

How to run:
cd .\harvestpulse
php -S localhost:8000

Team
Built at VUT Hackathon 2026 in 24 hours by the HarvestPulse team.

Licence
MIT Licence: free to use, fork, and build on.


"We built this in 24 hours because South Africa's farmers couldn't afford for us to wait any longer."
