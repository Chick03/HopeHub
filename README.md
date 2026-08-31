# HopeHub — Orphanage Donation Management System

A centralized, web-based platform that replaces manual, error-prone
donation record-keeping with a transparent, real-time system connecting
donors with **your orphanage**.

This edition manages exactly **one** orphanage (not a multi-orphanage
marketplace) — matching the project scope. Built with:
**PHP (PDO) + MySQL backend, HTML/CSS/JavaScript frontend**, per the
Software Requirements slide in the presentation.

---

## 1. Important: what "real" means here

Payments go through the **real Razorpay Checkout** — real API calls to
Razorpay's servers, their real hosted payment widget, real cryptographic
signature verification. That's a genuine payment gateway integration,
not a custom fake.

The one thing it still doesn't do is **move real money**, and that's
true regardless of where you host it — it's controlled entirely by
which Razorpay keys you use:

- **Test keys** (`rzp_test_...`, the default): only Razorpay's published
  test card/UPI numbers work. Real cards are rejected by Razorpay itself.
  No business verification needed — sign up and generate these in ~2 minutes.
- **Live keys** (`rzp_live_...`): move real money. Only issued after
  Razorpay approves your business KYC (PAN, bank account, business proof).

You flip between them by changing two constants in
`config/razorpay_config.php` — nothing else in the codebase changes.
Until you deliberately add live keys, there's no path for real money to
move, no matter how the site is deployed or labeled.

## 2. What's real vs. simulated

| Feature | Status |
|---|---|
| Donor / Admin login | **Real Google OAuth 2.0** — plug in your own free Google Cloud credentials |
| Database, CRUD, donation tracking, reports | **Fully real** — real PDO/MySQL logic |
| Public Top Donors leaderboard | **Fully real** — served as JSON from `api/leaderboard.php`, rendered by JS on the homepage |
| Payment gateway | **Real Razorpay integration**, in test mode by default (see section 1) |
| Email/SMS notifications | **Simulated** — logged to the `notifications` table and `notifications.log` instead of a real mail/SMS API |
| PDF receipts | **Fully real** — generated on the fly by a dependency-free PDF writer (`includes/SimplePDF.php`) |

## 3. Setup (XAMPP / WAMP)

1. Copy the `hopehub` folder into your web root, e.g. `C:\xampp\htdocs\hopehub`
   (must be named exactly `hopehub` — every link is `/hopehub/...`).
2. Start **Apache** and **MySQL** in the XAMPP control panel.
3. Open `http://localhost/phpmyadmin` → **Import** → select
   `database/hopehub.sql` → **Go**. The script safely drops and rebuilds
   its own tables first, so it's fine to re-import over an older version.
4. `config/db.php` already matches a stock XAMPP install (`root`, no
   password). Edit only if your MySQL setup differs.

## 4. Set up Razorpay (free test keys, ~2 minutes)

1. Sign up at [dashboard.razorpay.com/signup](https://dashboard.razorpay.com/signup).
2. Confirm the toggle top-left of the dashboard says **Test Mode**.
3. Go to **Settings → API Keys → Generate Test Key**.
4. Copy the **Key Id** and **Key Secret** into `config/razorpay_config.php`.
5. To actually complete a test payment on checkout, use Razorpay's
   published test cards/UPI IDs — real ones won't work in test mode:
   [razorpay.com/docs/payments/payments/test-card-upi-details](https://razorpay.com/docs/payments/payments/test-card-upi-details/)

## 5. Set up Google OAuth login (free, ~5 minutes)

Both Donor and Admin log in through the same Google button — your
**role comes from your email address in the `users` table**, not from
anything you choose at login.

1. Go to [console.cloud.google.com/apis/credentials](https://console.cloud.google.com/apis/credentials).
2. **Create Credentials → OAuth client ID** → type: **Web application**.
3. Under **Authorized redirect URIs**, add exactly:
   `http://localhost/hopehub/auth/google_callback.php`
4. Copy the **Client ID** and **Client Secret** into `config/oauth_config.php`.
5. To make yourself admin: in phpMyAdmin, open the `users` table, edit
   the seeded row (or add one), set `email` to **your real Gmail
   address**, `role` = `admin` — do this *before* you first sign in with
   that account. Everyone else who signs in becomes a Donor automatically.

## 6. Customize your orphanage's details

Log in as admin, then open **Orphanage Profile**
(`admin/orphanage_profile.php`) and replace every placeholder with your
real details: name, address, description, founder name/bio, trust or
society registration number, email, phone, and WhatsApp number. These
all show on the public homepage as trust/transparency signals for
donors. Use **Children & Needs** (`admin/manage_content.php`) to
add/remove the children in care and manage the internal needs list.

## 7. Project structure → System Architecture diagram

The folders map directly onto the boxes in your System Architecture
slide, so you can point straight at the diagram in your viva and name
the file behind each box:

| Diagram box | Code |
|---|---|
| User Authentication | `auth/` (Google OAuth flow) |
| Donor Portal | `donor/`, `index.php` |
| Admin Dashboard | `admin/` |
| Web Application Server (PHP) | every `.php` file's PDO logic, plus `api/leaderboard.php` as a dedicated JSON endpoint the frontend JS calls directly |
| Donation Processing | `donor/process_donation.php` |
| Payment Gateway | `payment/checkout.php`, `payment/razorpay_helper.php`, `payment/verify_payment.php` |
| Receipt Generation | `receipt/download.php`, `includes/SimplePDF.php` |
| Notification Service | `includes/notify.php`, `donor/notifications.php` |
| Reports & Analytics | `admin/reports.php`, `admin/export_report.php` |
| Needs / Wishlist | `admin/manage_content.php` (managed internally; not shown on the public homepage — see below) |
| Core Database (MySQL) | `database/hopehub.sql` |

```
hopehub/
├── config/            # DB connection, Google OAuth, Razorpay credentials
├── includes/          # Header/footer, auth guards, helpers,
│                      # notification module, PDF receipt generator
├── auth/              # Google OAuth login flow
├── api/               # JSON endpoints called by frontend JS (fetch),
│                      # not rendered as HTML — e.g. the leaderboard
├── donor/             # Donor Module — dashboard, donate, history, notifications
├── admin/             # Admin & Reporting Module — profile, children/needs,
│                      # verification, reports, users
├── payment/           # Payment Module — Razorpay checkout + verification
├── receipt/           # Receipt generation/download
├── assets/            # CSS + JS (main.js calls api/leaderboard.php)
├── database/          # hopehub.sql (schema + seed data)
└── index.php          # Public homepage — about, trust info, leaderboard, donate CTA
```

## 8. Modules → files

- **Donor Module** — `auth/`, `index.php`, `donor/dashboard.php`
- **Donation Management Module** — `donor/donate.php`, `donor/process_donation.php`, `donor/donation_history.php`
- **Payment Module** — `payment/checkout.php`, `payment/razorpay_helper.php`, `payment/verify_payment.php`
- **Notification Module** — `includes/notify.php`, `donor/notifications.php`
- **Admin & Reporting Module** — everything in `admin/`

## 9. How the payment flow actually works

1. Donor submits a Cash donation → `donor/process_donation.php` creates
   a `pending` row → redirects to `payment/checkout.php`.
2. `checkout.php` calls Razorpay's Orders API server-side
   (`razorpay_helper.php::razorpayCreateOrder()`) and stores the
   returned `order_id` on the donation row.
3. Clicking Pay opens **Razorpay's own hosted Checkout widget**
   (loaded from `checkout.razorpay.com`) — card/UPI/bank details are
   typed into Razorpay's PCI-compliant UI, never into this app's forms.
4. On completion, Razorpay's widget calls a JS handler with a
   `payment_id`, `order_id`, and `signature`, which gets POSTed to
   `payment/verify_payment.php`.
5. `verify_payment.php` does three server-side checks before marking
   anything successful: the donation belongs to the logged-in donor and
   is still pending; the returned `order_id` matches the one *we*
   created for *this* donation (stops a valid signature from an
   unrelated payment being replayed here); and the HMAC-SHA256
   signature is valid, proving Razorpay itself generated it. Only then
   does it insert the payment/receipt records and mark the donation
   `success`.

## 10. Design notes

- **Cash vs. in-kind donations** are handled differently: cash goes
  through Razorpay Checkout immediately; food/clothes/books/medical
  donations are logged `pending` until an admin verifies physical
  receipt (`admin/verify_donations.php`, which now only queues in-kind
  donations — cash is confirmed automatically by Razorpay). This is
  what `Admin.verifyDonation()` in the class diagram maps to.
- **Public homepage doesn't show a site-wide total-donations figure.**
  Instead it shows a **Top Donors leaderboard** — ranked by each
  donor's own confirmed cash total, fetched live from `api/leaderboard.php`.
- **"Current Needs" isn't displayed on the homepage.** The `needs`
  table and its admin management still exist (per the Needs/Wishlist
  box in the architecture diagram) — it's an internal admin tool now,
  not a public section. Donors can still optionally link a donation to
  an open need from the donation form.
- **Trust & Contact section**: founder name/bio, registration number,
  email, phone, and a click-to-chat WhatsApp button — all editable from
  `admin/orphanage_profile.php`.
- Reports include a monthly donation trend chart (Chart.js), a
  needs-fulfillment summary, and CSV export — this is where site-wide
  totals still live, since the Admin & Reporting Module explicitly
  calls for "View Total Donations."
"# HopeHub" 
"# HopeHub" 
"# HopeHub" 
"# HopeHub" 
