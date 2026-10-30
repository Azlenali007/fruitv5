# N.A Fresh Fruits & Coconuts — Store, Admin Portal & Razorpay Gateway

Production-ready PHP 8.2+ and MySQL web application for **N.A Fresh Fruits & Coconuts**, built to match the approved brand visual specifications with an integrated, secure native **Razorpay Payment Gateway** and **Cash on Delivery (COD)** checkout system.

---

## 🌴 System Features

### Customer Storefront
- **Reference-Accurate Visual Design**: Pixel-matched emerald palette (`#235d3a`), typography, badge hierarchy, spacing, stone-slab hero visuals, and card layouts.
- **Fresh Tender Coconut Water**: Single-product focus with live prices (`₹40 /pc`), real-time MySQL inventory status, stepper quantity selector, and live subtotal calculator.
- **Shopping Cart**: Real-time server-side stock validation, session persistence, line total recalculation, and empty-state handling.
- **Local Delivery & Shop Pickup Checkout**: Full Indian mobile number validation, full address inputs with inline editor, and Cash on Delivery / Online Payment selection.
- **Fixed Mobile Bottom Navigation**: Always-visible viewport-fixed bottom navigation (`Home`, `Shop`, `Cart` with live counter badge, `Profile`/`Track Orders`) with safe-area insets.
- **Order Tracking & Confirmation**: Displays unique order number (e.g., `#NA1024`), visual preparation/delivery timeline, estimated delivery times, and status logs.

### Razorpay Payment Gateway (Native PHP 8.2+)
- **Zero External Dependencies**: Built 100% in native PHP 8.2 using `cURL` and `hash_hmac('sha256', ...)` — NO Composer SDK required, NO `vendor/` dependency.
- **Independent Server-Side Calculations**: Order amounts are calculated securely on the server in integer paise (`₹120.00 = 12000 paise`). The client cannot manipulate payment amounts.
- **Standard Checkout Modal**: Official Razorpay Checkout script (`checkout.js`) with responsive UPI (GPay, PhonePe, Paytm), Cards, NetBanking, and Wallets.
- **HMAC SHA256 Signature Verification**: Server validates payment signature before updating orders.
- **Amount & Currency Validation**: Re-queries Razorpay Orders/Payments API to verify exact amount (`INR` paise match) and payment status before marking captured.
- **Cart Protection**: Cart items remain preserved during checkout attempts and are only cleared after successful payment verification.
- **Idempotent Webhook Engine (`/webhooks/razorpay`)**: Receives automated gateway events with signature verification and deduplication (`payment_webhook_events` table).
- **Separated Order vs Payment Statuses**: Clean lifecycle tracking (`order_status`: `pending`, `confirmed`, `delivered`, `cancelled`; `payment_status`: `pending`, `authorized`, `captured`, `failed`, `refunded`).

### Admin Management Panel (`/admin`)
- **Protected Access**: Throttled login protection (5 attempts / 15 minutes), bcrypt password verification, CSRF token validation, session ID regeneration upon login, and secure cookies.
- **Operational Dashboard**: Live database metrics for Orders Today, Sales Today (excluding cancellations), Pending, Confirmed, Delivered, and Cancelled counts, plus a 7-day sales trend powered by Chart.js.
- **Order Management**: Search by order number, customer name, phone; filter by status and date range; review full historical item price snapshots; update statuses with audit trail comments.
- **Payment Transactions Audit (`/admin/payments`)**: Searchable, filterable ledger of all Razorpay payment attempts and COD records, with transaction inspection modal.
- **Payment Gateway Settings (`/admin/payment-settings`)**: Toggle Razorpay ON/OFF, toggle Cash on Delivery ON/OFF, select Test/Live mode, enter API credentials with masked secrets, copy Webhook URL, and run live API connection diagnostics.
- **Atomic Stock Restoration**: Automatic stock inventory replenishment upon order cancellation.
- **Product & Stock Management**: Update live price per piece, stock quantities, availability toggle, description, and product imagery.
- **Delivery & Business Settings**: Configure delivery charges, minimum order amount for delivery, free delivery threshold, and served delivery localities.
- **Financial & Sales Reports**: Filterable date-range revenue analytics, payment method breakdown (COD vs Online), and order status doughnut chart.

---

## 💳 Razorpay Dashboard Configuration Guide

### 1. Generating API Keys (Test / Live)
1. Log in to your [Razorpay Dashboard](https://dashboard.razorpay.com/).
2. Navigate to **Settings** &rarr; **API Keys**.
3. Under **Key Generation**, generate your Key ID and Key Secret:
   - For testing: Select **Test Mode** &rarr; Keys start with `rzp_test_...`
   - For production: Select **Live Mode** &rarr; Keys start with `rzp_live_...`
4. Copy your **Key ID** and **Key Secret**.
5. In the Store Admin Portal, go to **Admin** &rarr; **Payment Gateway** (`/admin/payment-settings`).
6. Paste your credentials, select your environment mode, and click **Save Payment Gateway Settings**.
7. Click **Test Razorpay Connection** to verify immediate connectivity.

### 2. Setting Up Razorpay Webhooks
1. In the Razorpay Dashboard, go to **Settings** &rarr; **Webhooks** &rarr; **Add New Webhook**.
2. **Webhook URL**: Enter your store's live webhook endpoint:
   ```
   https://YOUR-DOMAIN.com/webhooks/razorpay
   ```
3. **Secret**: Enter a secure random string (e.g. `na_wh_sec_` + random characters).
4. **Active Events**: Check the following required events:
   - `payment.captured`
   - `order.paid`
   - `payment.failed`
   - `refund.processed`
5. Save the Webhook in Razorpay, then copy the exact Webhook Secret into your store's **Payment Gateway Settings** (`/admin/payment-settings`).

---

## 🛠️ Architecture & Directory Structure

```
├── app/
│   ├── bootstrap.php                 # App bootstrap, CSRF, XSS escaper, sessions
│   ├── config/
│   │   ├── config.php                # App and database configuration
│   │   ├── database.php              # PDO connection singleton
│   │   └── payment_config.php        # Protected Razorpay credentials (outside web root)
│   ├── models/
│   │   ├── Admin.php                 # Admin auth & login throttling
│   │   ├── Coupon.php                # Coupon codes, rules, percentage/fixed calculations
│   │   ├── Customer.php              # Customer accounts, addresses, order history
│   │   ├── Order.php                 # Orders, atomic stock management, status history
│   │   ├── PaymentTransaction.php    # Transaction audit ledger & webhook deduplication
│   │   ├── Product.php               # Product catalog and stock management
│   │   └── Setting.php               # Store settings key-value store
│   ├── services/
│   │   ├── AuthService.php           # Admin authentication session service
│   │   ├── CartService.php           # Server-authoritative cart and discount calculations
│   │   ├── CustomerAuthService.php   # Customer login, registration, and privacy isolation
│   │   ├── OrderService.php          # Checkout coordinator, paise calculations, and orders
│   │   └── RazorpayService.php       # Native cURL Razorpay API, signature verification, webhooks
│   └── views/
│       ├── admin/                    # Admin portal views (orders, payments, settings, etc.)
│       ├── customer/                 # Customer account, login, and registration views
│       ├── layouts/                  # Storefront header, footer, bottom navigation
│       ├── cart.php                  # Interactive shopping cart view
│       ├── checkout.php              # Checkout with Razorpay Modal and COD selector
│       ├── confirmation.php          # Order confirmation and tracking timeline
│       ├── home.php                  # Primary landing and coconut water storefront
│       └── shop.php                  # Shop view
├── database/
│   ├── migrations/
│   │   ├── 001_customer_accounts.sql
│   │   ├── 002_coupons_and_whatsapp_popup.sql
│   │   └── 003_razorpay_payment_gateway.sql
│   ├── schema.sql                    # Full consolidated database schema (14 tables)
│   └── setup.php                     # Database seeder and verification script
├── public/
│   ├── assets/                       # CSS, icons, images, brand assets
│   ├── favicon.ico
│   ├── index.php                     # Public front controller & router
│   ├── manifest.json                 # Web App Manifest
│   └── sw.js                         # Service Worker
├── storage/                          # Logs, uploads, installation lock
├── tests/
│   ├── run_tests.php                 # Master test suite runner (122 automated tests)
│   ├── test_coupons_and_whatsapp.php # Promo coupon and WhatsApp popup tests
│   ├── test_customer_auth.php        # Customer authentication and privacy tests
│   ├── test_database_init.php        # Database schema tests
│   ├── test_installer.php            # Web installer tests
│   └── test_razorpay.php             # Comprehensive 18-scenario Razorpay & COD tests
└── server.ts                         # Node.js dev gateway hosting Vite & PHP FastCGI
```

---

## 🧪 Automated Verification Suite

Run the full automated test suite containing 122 end-to-end tests:

```bash
php tests/run_tests.php
```

All 122 tests cover:
- Database schema and initial inventory integrity
- Cart and price calculation rules
- Order creation and atomic stock decrement / restoration
- Admin authentication, rate limiting, and password security
- CSRF protection and XSS escaping
- Customer authentication, profile isolation, and order privacy
- Coupon discount mechanics (free delivery, percentage, flat discount, usage limits)
- WhatsApp modal frequency and configuration
- Razorpay order creation (integer paise, receipt generation)
- Cryptographic HMAC SHA256 payment signature verification
- Tampered signature rejection
- Amount and currency mismatch detection
- Idempotent duplicate verification handling
- Webhook signature verification and event deduplication
- Cash on Delivery end-to-end checkout flow
