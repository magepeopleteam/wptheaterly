# Theaterly operations

The existing Theaterly main menu is the operational workspace. There is one ticket sale flow (New Ticket Sale), one booking editor (Bookings), and one reporting workspace (Sales Report). Check-in, Booking Policies, Change Requests and History are additional tabs. Both legacy Operations URLs redirect to Check-in in the main menu.

## Customer ticket dashboard

`/my-theater-tickets/` and the WooCommerce My Account “Theater tickets” endpoint display owned bookings. Guests can use their confirmation's order number, billing email and order key. Ticket links use private responses and order-bound access checks. The dashboard provides movie/order search, upcoming/past/cancelled filters, ticket status, show time, seats, payment link for unpaid reservations, PDF download, local entry QR, calendar export, email resend, seat cancellation and rescheduling. Customer changes obey Booking Policies. All customer cancellations and rescheduling first create a private request. Managers approve or reject these in Change Requests. Pending and rejected requests leave the original order, seats, price and ticket intact. Approval rechecks the ticket version and seat availability; more expensive changes then require payment. Customers see pending/approved/rejected status and any manager reply on their ticket card. Changes after check-in are refused; a new ticket version invalidates previous codes.

## Operational features

| Feature | Implementation |
| --- | --- |
| Duplicate/retry protection | Request UUID, order/booking locks, unique seat claims and idempotent WooCommerce synchronization. Classic and Store API checkout callbacks use the same booking service. |
| Booking edits | Server-side seat/pricing validation; WooCommerce line-item metadata, count, active ticket value, layout snapshot and ticket version stay synchronized. Price increases create a separate payment order before applying the change. |
| Scheduling | Theater overlap validation includes movie duration, recurring dates, off days, overnight shows and a cleanup buffer. |
| Booking window | Configurable advance days, booking cutoff and temporary seat holds. |
| Box office | Cash, card-terminal, manual payment or unpaid reservation; cash tender/reference checks, printable receipt, payment collection and cashier report. |
| Check-in | Locally generated signed QR codes; local camera decoder, manual verification using QR token or `BK` booking number, screen/date checks, payment checks and repeat-entry rejection. The customer QR covers all active seats in the booking. |
| Ticket email | Payment/status-triggered delivery, private PDF attachment, fingerprint deduplication, customer/staff resend, three retry attempts and event history. |
| Cancellation/refunds | Partial seat cancellation, weighted price allocation, proportional cancellation tax refund, automatic gateway refund when supported, explicit manual-reimbursement order notes otherwise. |
| Rescheduling | Available-seat/show selector, authoritative quote and payment-before-confirmation for price increases. Failure to refund preserves original seats. |
| Customer access | Customer ownership or order-key checks; public booking CPT queries are disabled. |
| Reports | Validated custom date ranges, movie/screen/status filters, payment/show date basis, paid ticket counts, gross/refund/net values and separate sale/show dates. |
| Exports | PDF, Excel-compatible SpreadsheetML `.xls`, and print output use the same filtered report data. |
| Seat-plan protection | Future booked seat identities cannot be removed or renamed; bookings retain a layout snapshot and the last 20 plan versions are retained. |
| Staff access/history | `wtbm_manager`, `wtbm_cashier`, `wtbm_admission` roles, separate manage/sell/admit/reports capabilities, booking/admission/payment/email event history. |

The Bookings table shows Not checked in, Partially checked in or Checked in using the current active seat allocation. Check-in details show human seat names, the time in the WordPress site timezone and the staff member for each check-in group. The existing `wtbm_admission` URL and permission keys remain compatible.

## Configuration and storage

Booking Policies stores `wtbm_operations_settings`. Defaults: 30 advance days, 15-minute booking cutoff, 15-minute seat hold, 15-minute cleanup buffer, 120-minute customer-change cutoff, customer changes enabled. The plugin creates the customer ticket page once (`wtbm_tickets_page`), registers the `theater-tickets` account endpoint and tracks schema installation with `wtbm_operations_schema`.

Customer requests use the private, non-public/non-REST `wtbm_change_request` CPT with `_wtbm_booking`, `_wtbm_order`, `_wtbm_kind`, `_wtbm_payload`, `_wtbm_version`, `_wtbm_status`, `_wtbm_reply`, reviewer/time and optional price-difference payment-order metadata. The manager queue prioritizes the oldest 100 pending requests, followed by the latest 20 decisions; pending requests move forward as earlier ones are reviewed.

Staff receipts are read-only and require the sell capability. Raw receipt URLs work in AJAX responses and redirects, avoiding HTML-escaped query separators; old staff receipt links remain accessible after the optional navigation nonce expires. The handler also accepts the old `amp;order_id` parameter.

Tables `{prefix}wtbm_claims` and `{prefix}wtbm_events` hold seat allocation/admission and operational history. Existing booking CPT data remains compatible. The hourly `wtbm_expire_claims` job clears expired holds; `wtbm_deliver_ticket` schedules mail delivery and retries. A dependable WordPress cron runner is required for unattended delivery. PDF generation uses the installed MagePeople mPDF dependency and a private writable temporary cache. Local QR dependencies and their licenses are under `assets/vendor/`.

## Financial interpretation and limits

Report date filters select an order cohort by the original payment date, or its show date. Net figures include that cohort's currently recorded refunds. They are not a cash-flow ledger of refunds occurring inside the selected dates. Separate price-difference payments are reflected in the changed ticket value; source-order gateway refunds never exceed the original captured amount. Refunds beyond that balance require manual reconciliation across the separate payment orders. Cash/manual refunds record the liability and require actual reimbursement; they do not transfer funds. Lower-priced rescheduling currently refunds the ticket price difference; tax changes on rescheduling require review in WooCommerce. Cancellation tax refunds are proportional to the selected seats.

Seats for a more expensive replacement show are checked again on payment completion; a newly unavailable seat or changed ticket version requires staff review of the paid change order. A camera needs browser permission and a secure context (localhost is supported). Live payment-provider refund behavior, real SMTP delivery and physical-camera scanning must be verified against the site's configured services before production use.

## Validation

Run isolated, self-cleaning integration fixtures with:

```sh
wp --path=/var/www/html/magepeople eval-file /var/www/html/magepeople/wp-content/plugins/wptheaterly/tests/operations-integration.php
```

81 checks cover authoritative pricing, invalid/duplicate seats, canonical show times, hold competition/expiry, request retries, WooCommerce callback synchronization, protected layouts, overlaps, edits, extra-payment application/retry, refund failure rollback, cancellations, private access, revoked/forged QR, repeated admission, report reconciliation/date basis, calendar UTC times, connected customer controls, PDF/email deduplication staff permissions, approval/rejection without pre-approval mutations, duplicate requests, stale-ticket review prevention, approved rescheduling/payment, customer request status, guest request access, booking-number admission/date validation and raw receipt URLs. Email transport is intercepted and fixtures are removed; no live customer emails are sent by these tests.

Additional checks: 79 PHP files pass syntax checks; the six new service/admin modules have zero WPCS errors (60 warnings remain, primarily direct custom-table queries, uncached allocation reads and metadata-query performance notices). All PHP syntax, changed JavaScript syntax, whitespace, PDF/Excel/print generation, local QR encoder/decoder compatibility, browser dashboard filters/QR rendering, main-menu tab navigation and payment-method field visibility. Responsive styles are included; physical mobile-device and external-service verification remain separate from these local checks.

Booking filters use canonical WooCommerce statuses, normalized show times, exact movie/screen/date matching and stable AJAX pagination. Filter changes and Clear refresh results without reloading; requests are cancelled when superseded. PDF/CSV export all matching bookings, including draft/cancelled records, independently of the loaded page. Downloads use short-lived tokens bound to the requesting staff member and capability/nonce checks. CSV includes UTF-8 BOM and spreadsheet formula protection; both exports include human seat labels and check-in status. CSV additionally includes grouped check-in time/staff details. PDF uses the private mPDF cache and landscape layout.

Customer contacts in booking exports use saved booking fields, fall back to legacy `_wtbm_user_name` / `_wtbm_user_phone` order-item metadata and then WooCommerce billing details. An email used as a legacy name is replaced with the saved attendee name where available. Missing contacts are marked Not provided. Existing customer records are not changed by export.

History includes manager-only AJAX clearing with age ranges (7/30/90/365 days or all), an entry-count preview and explicit confirmation. A short-lived, user-bound preview token freezes the highest eligible event ID and UTC cutoff, preserving activity arriving after the preview. History deletion affects only `wtbm_events`; booking, payment and seat/check-in records remain intact. `wtbm_history_retention` stores enabled/days/daily local time (default disabled, 90 days, 02:00). The `wtbm_cleanup_history` single-event hook reschedules each run at the selected WordPress site time, including timezone/DST changes. A reliable WordPress cron runner is needed for timely execution. `wtbm_history_last_cleanup` stores the last run timestamp and deleted count.

Staff receipts use `templates/box-office-receipt.php` and dedicated CSS/JS: customer/legacy attendee contacts, show/seat/quantity lines, order-currency totals, tax/discount/fees/refunds and cash tender/change. The currency and change calculation use the order currency and original captured total. A4/Save PDF and 80 mm print controls hide navigation and use printable monochrome styles; 80 mm printing requires the matching paper width in the printer driver. The receipt remains read-only and protected by the existing staff sell capability. Physical-printer output is not hardware-certified locally.

Pricing uses one server evaluator for quotes, counter orders, public checkout and changes. Rules normalize weekday names and boolean combination flags, sort by priority with an ID tie-break, preserve minutes in time ranges, support overnight ranges, and ignore malformed date/time/multiplier values. An empty theater type means all selected theaters. Calendar badges show increases/decreases and minimum seat quantities before selection; AJAX updates them for the chosen theater, time and actual seat count. The hold quote updates both visible and submitted totals and shows base price, adjustment and applied rule names. Animated seat copies are inert so they cannot intercept selection. Orders preserve `_wtbm_pricing_details` snapshots, readable order metadata and receipt breakdowns; existing orders are never repriced retrospectively.

Pricing validation: `wp eval-file wp-content/plugins/wptheaterly/tests/pricing-rules.php` runs 16 isolated checks, restoring the in-memory rule cache without changing live rules/orders. The operations integration suite now runs 82 checks, including an immutable pricing snapshot and authoritative recalculation when reducing booked seats. Browser verification confirmed public and counter-calendar weekday badges, minimum quantities and the public three-seat summary (210 − 42 = 168). Temporary browser preview holds were removed.
