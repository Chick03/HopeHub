<?php
/**
 * Razorpay configuration.
 *
 * Get your TEST keys (free, no business verification needed, ~2 minutes):
 *   1. Sign up at https://dashboard.razorpay.com/signup
 *   2. Once in the dashboard, make sure the toggle top-left says "Test Mode"
 *   3. Go to Settings -> API Keys -> Generate Test Key
 *   4. Copy the Key Id and Key Secret into the two constants below
 *
 * Test mode is fully real Razorpay infrastructure (their real API, their
 * real Checkout widget) — it just refuses to move real money. You can only
 * "pay" using Razorpay's published test card/UPI numbers, listed at:
 * https://razorpay.com/docs/payments/payments/test-card-upi-details/
 *
 * To eventually accept real donations, you complete Razorpay's business
 * KYC (PAN, bank account, business proof) to unlock LIVE keys, then just
 * replace the two values below with your rzp_live_... keys. Nothing else
 * in this codebase needs to change.
 */
define('RAZORPAY_KEY_ID', 'rzp_test_TWNTAfmNDli2y2');
define('RAZORPAY_KEY_SECRET', '2RqP5Qu67dD2rUL4C50ANl2r');

define('RAZORPAY_API_BASE', 'https://api.razorpay.com/v1');
