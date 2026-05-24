# PRD — E-Tourism Information System for CV Kopi Danau Atas

> **Version:** 2.0 (major revision)
> **Date:** May 23, 2026
> **Author:** Fadhil Dzaky Arhab — 2301092010
> **Thesis Title:** Design and Development of a Web-Based E-Tourism Information System for CV Kopi Danau Atas

---

## Changelog (v1.0 → v2.0)

This is a major revision that reconciles the PRD with the implementation
that has been delivered. Items below describe **what changed since v1.0**:

- **§4.6 Review system → transparent / auto-publish.** Reviews are now
  published immediately on submission. Admin moderation
  (pending/approved/rejected) was removed in favour of transparency. The
  only admin action available is deleting spam/abusive content.
- **§4.8 Authentication → Google OAuth + email verification added.**
  Users can register/login with a Google account. Manual registrations
  must verify their email before booking.
- **§4.4 Booking flow → "Review Order" step added.** Between package
  detail and checkout, users now fill in guest details (name, phone,
  email, optional notes) and accept the Terms & Conditions.
- **§4.4.4 E-Ticket → PDF Invoice download added.** Paid bookings can
  also download a printable invoice PDF in addition to the QR code
  e-ticket.
- **§4.4 Auto-completion → added.** Paid bookings whose visit date has
  passed are automatically marked `completed` once per day so the user
  can submit a review.
- **§4.9.5 Admin reviews UI** updated to read-only inspect + delete-only.
- **§4.9.1 Dashboard stat** "pending reviews" replaced with "total
  reviews".
- **§3.1 Guest, §4.5 Blog, §4.9.4 Blog management, §4.9.7 User
  management** — affected sub-features are now flagged as **Roadmap**
  rather than v1.0 deliverables (see §12).

---

## 1. Executive Summary

CV Kopi Danau Atas is an Arabica Solok coffee processing and trading
company located in the Danau Diatas highland area, Alahan Panjang. The
company holds significant agrotourism potential, yet still manages
reservations manually via notebooks and WhatsApp — leading to
double-booking incidents and limited tourist information availability.

This system is an **integrated E-Tourism website** that digitizes the
entire tour package reservation process, presents a coffee education
catalog, integrates location maps, and provides a visitor review system
— powered by the **Midtrans payment gateway** for automated payments and
**Google OAuth** for fast onboarding.

---

## 2. Product Objectives

| # | Objective | Success Metric |
|---|-----------|----------------|
| 1 | Eliminate double-booking | 0% schedule overlap incidents |
| 2 | End-to-end reservation digitization | 100% bookings through the website |
| 3 | Automated payment via Midtrans | Real-time payment verification without manual intervention |
| 4 | Enhance tourist information | Visitors receive comprehensive info (maps, blog, coffee catalog) before visiting |
| 5 | Collect transparent visitor feedback | Reviews are published instantly without moderation gatekeeping |
| 6 | Reduce signup friction | Google OAuth in addition to email/password registration |

---

## 3. Users & Roles (Actors)

### 3.1 Guest (Non-Authenticated Visitor)
- Browse the homepage, tour packages, and blog
- View location map and visitor reviews (all visible — no moderation queue)
- Switch language (Indonesian ↔ English)
- Access login and registration pages (email/password or Google)
- _(Roadmap §12)_ Browse coffee variety catalog as a public page

### 3.2 Registered Tourist (Authenticated User)
- All Guest access rights
- Email must be verified (or registered via Google, which is
  pre-verified) before booking
- Book/reserve tour packages (with Review Order step + T&C acceptance)
- Make payments via Midtrans (Snap)
- View booking history, payment status, and download invoice PDF
- Submit reviews and ratings after completed visits — **published
  immediately**
- Manage account profile (name, email, phone, password)

### 3.3 Admin (CV Kopi Danau Atas Manager)
- Filament PHP dashboard access
- Manage tour packages (CRUD + daily quota + multi-image upload)
- Validate/approve/reject bookings (status transitions)
- Manage blog/articles (categories only in v2.0; tags on roadmap)
- Manage homepage content (hero, about, education, testimonials,
  documentation gallery)
- Manage coffee variety catalog (admin-only in v2.0; public page on
  roadmap)
- **Inspect & delete** visitor reviews (no approve/reject — auto-publish)
- View visit reports & payment transactions
- _(Roadmap §12)_ Block/unblock users

---

## 4. Features & Functional Specifications

### 4.1 Navbar (Header Navigation)

The navbar is always visible across all public pages with a fixed/sticky
layout.

| Element | Position | Description |
|---------|----------|-------------|
| **Logo** | Left | CV Kopi Danau Atas logo, click → homepage |
| **Beranda** (Home) | Main menu | Link to the homepage |
| **Paket Wisata** (Tour Packages) | Main menu | Link to tour packages listing |
| **Blog** | Main menu | Link to blog/coffee journal page |
| **Masuk** (Sign In) | Right | Text link button for login |
| **Daftar** (Sign Up) | Right | Primary filled button for registration |
| **🌐 ID / EN** | Far right | Dropdown/toggle for language switching |

> **Design Reference:** Similar to the AYO.co.id navbar — clean,
> minimalist, with a "Daftar" button in accent color (dark green / forest
> green matching the coffee theme).

---

### 4.2 Homepage (Landing Page)

The main page consists of multiple vertically flowing sections:

#### Section 1: Hero Banner
- **Background:** Full-width, high-resolution photo of the coffee
  plantation / Danau Diatas scenery
- **Overlay:** Semi-transparent dark gradient
- **Headline:** Large, bold text — e.g., *"Explore the Beauty of Solok
  Coffee Agrotourism"*
- **Sub-headline:** Brief 1–2 sentence description
- **CTA Button:** "Book Now" → navigates to tour packages page
- **All hero text & images are editable by admin** via the Filament panel

#### Section 2: About Us / Brief Information
- Layout: Text on the left + image grid on the right
- **Section title** (admin-editable)
- **Description paragraph** (admin-editable)
- **Image grid** (3–5 photos of the plantation, coffee process, etc. —
  managed by admin)
- **"Learn More" button** → links to tour packages or about page

#### Section 3: Featured Tour Packages
- Displays **3–4 most popular tour packages** in a card layout
- Each card contains:
  - Package photo
  - Package name
  - Starting price
  - Average rating
  - "View Details" button
- Admin controls which packages are displayed as "featured"

#### Section 4: Documentation & Education
- Layout: Large photo/mockup on the left + content on the right
- Displays **documentation photos** of tourists performing educational
  activities (coffee picking, roasting, cupping)
- Title: *"Coffee Education Experience"* (editable)
- Description of available educational activities
- Feature bullet points with green checkmark icons
- _(Roadmap §12)_ Carousel/slider navigation `01 / 03` with arrows

#### Section 5: Visitor Reviews (Testimonials)
- Layout: Dark green/themed card on the left with *"What Our Visitors
  Say"* title + review card on the right
- Displays the latest visitor reviews (no moderation queue):
  - **Reviewer name** (or Google avatar)
  - **Tour package context**
  - **Review text** (quote)
  - **Star rating** (1–5)
- Carousel navigation with arrows
- Reviews appear immediately after submission

#### Section 6: Location Map
- Embedded **Google Maps** showing CV Kopi Danau Atas location
- Full address beside/below the map
- "Open in Google Maps" button (redirects to Google Maps)

#### Section 7: Footer
- Logo + brief company description
- Quick navigation links (Home, Tour Packages, Blog, Contact)
- Contact information (address, phone, email, WhatsApp)
- Social media icons (Instagram, Facebook, TikTok)
- Copyright © 2026 CV Kopi Danau Atas

---

### 4.3 Tour Packages Page

#### 4.3.1 Package Listing
- Grid/list cards of all available tour packages
- Each card displays:
  - Main photo
  - Package name
  - Duration
  - Price per person
  - Daily capacity/quota
  - Remaining slots today (real-time)
  - Average rating (from all published reviews)
  - "Book" / "Details" button
- _(Roadmap §12)_ Filters: price range, duration, availability
- _(Roadmap §12)_ Sort: price, popularity, rating

#### 4.3.2 Package Detail Page
- Photo gallery (carousel)
- Package name, full description
- Detailed information: price, duration, capacity, schedule, facilities,
  important notes
- Booking entry-point form:
  - Select visit date (date picker, minimum: D+1)
  - Number of guests
  - Real-time validation: displays remaining quota on the selected date
  - If quota is full → disable and show "Quota full for this date"
  - "Continue" button → routes to **Review Order** page (§4.4.1)
- Visitor reviews section for this package (no moderation; latest first)

---

### 4.4 Reservation & Payment System

#### 4.4.1 Booking Flow (User Flow)

```
Select Package
  → Choose Date & Guest Count
  → Review Order  ← guest_name / guest_phone / guest_email / notes / T&C
  → Create Booking (status: pending)
  → Checkout (Midtrans Snap)
  → Payment
  → Webhook → status: paid
  → QR Code + Invoice PDF generated
  → Email Notification (queued)
  → Visit date passes → status: completed (auto, daily 23:55)
  → User can submit Review (auto-published)
```

The **Review Order** step is mandatory and ensures the actual visitor
information is captured separately from the account holder, supports
booking on behalf of others, and records explicit Terms & Conditions
acceptance.

#### 4.4.2 Midtrans Payment Gateway Integration

| Aspect | Detail |
|--------|--------|
| **Integration method** | Midtrans Snap (popup/redirect) |
| **Payment methods** | Bank Transfer (BCA, BNI, BRI, Mandiri), GoPay, ShopeePay, QRIS, Credit/Debit Card |
| **Payment flow** | Client → Laravel Backend (create transaction) → Midtrans API → Snap Token → Client Snap UI → Payment → Webhook → Update Status |
| **Webhook / Notification** | Endpoint receives Midtrans callbacks for automatic status updates |
| **Client polling fallback** | If the webhook is delayed, the booking detail page can call `POST /booking/{id}/update-status`, which queries Midtrans `Transaction::status()` and re-runs the same handler locally |
| **Transaction statuses** | `pending` → `paid` → `confirmed` / `expired` / `cancelled` / `refunded` (`completed` set later by scheduler) |
| **Security** | Signature key verification on every webhook |

#### 4.4.3 Quota Management

- Each package has a **daily capacity quota** (e.g., 20 persons/day)
- Remaining quota = `daily_capacity` minus the sum of `guest_count` for
  bookings on that `visit_date` whose `status` is one of
  `paid|confirmed|completed`, **plus** any `pending` bookings created
  within the last hour. Pending bookings older than 1 hour are
  auto-expired so they no longer block the quota.
- If requested guest count > remaining quota → reject with error message
- Bookings with `pending` status older than 1 hour → automatically
  expired by `bookings:expire-pending` (every 15 minutes)
- Bookings with `paid` / `confirmed` status whose `visit_date` has
  passed → automatically transitioned to `completed` by
  `bookings:auto-complete` (daily at 23:55), unblocking the review form

#### 4.4.4 E-Ticket and Invoice

When payment status reaches `settlement` (paid), the system generates:

- **Unique booking code** (format: `KDA-YYYYMMDD-XXXXX`)
- **QR Code** containing the booking code (stored in
  `storage/app/public/qrcodes/`)
- **Email confirmation** (queued, includes the QR code) sent to
  `guest_email` (falling back to the user's account email)

The user can:

- View the e-ticket on the **Booking Detail** page
- **Download a printable Invoice PDF** at
  `/booking/{id}/invoice` at any time after payment is settled

---

### 4.5 Blog Page

- Blog/article listing page (grid layout) with search and category filter
- Each card contains: thumbnail, title, publish date, category, excerpt,
  "Read More" button
- Article detail page: title, date, category, content (rich text),
  supporting images, share buttons, related articles (same category)
- **Admin** manages blog via Filament:
  - CRUD articles (rich text WYSIWYG)
  - Category management
  - Image upload
  - Status: Draft / Published
  - SEO fields: meta title, meta description
  - _(Roadmap §12)_ Tag management

> The blog covers coffee-related journals (Lini-S, Gayo, Typica
> varieties), visit tips, plantation news, and other educational content.

---

### 4.6 Review & Rating System (transparent / auto-publish)

- Registered tourists whose booking is `completed` can submit reviews
- Review form:
  - Star rating (1–5, required)
  - Text comment (min 10 characters, required)
  - Photo upload (optional, max 3 photos)
- **Reviews are published immediately** upon submission. There is no
  pending/approved/rejected workflow. This is a deliberate design
  decision to keep the platform transparent — admins cannot hide low
  ratings.
- The only moderation action available to admins is **deletion** of
  reviews that contain spam, hate speech, or otherwise abusive content.
- Published reviews appear on the homepage testimonials section and on
  the related tour package detail page.

> The `reviews.status` column is retained in the database for backward
> compatibility but is always set to `approved` in v2.0.

---

### 4.7 Multi-Language (Internationalization)

- **Default language:** Indonesian (ID)
- **Secondary language:** English (EN)
- Language toggle on navbar (globe icon + dropdown)
- Selection persisted in session via `SetLocale` middleware
- Scope of translation:
  - Static UI labels (navbar, buttons, placeholders, footer, etc.)
  - Dynamic content (tour packages, blog, homepage descriptions) → only
    UI labels are translated; content remains in the language entered
    by admin
- Implementation: Laravel Localization (`lang/id.json` & `lang/en.json`)

---

### 4.8 Authentication & Account Management

#### 4.8.1 Registration (Sign Up — email/password)
- Fields: Full name, Email, Password, Confirm Password
- Unique email validation
- After registration → an **email verification** link is sent. The user
  is redirected to the *Verify Email* notice page and **must click the
  link before they can access booking-protected routes** (Laravel's
  `verified` middleware enforces this)

#### 4.8.2 Login (Sign In)
- Email/password sign-in
- "Forgot Password" → reset link sent to email
- **Sign in with Google** button (single-tap OAuth)

#### 4.8.3 Login with Google (OAuth)
- Implemented via `laravel/socialite` and the Google driver
- Routes: `/auth/google/redirect` → Google → `/auth/google/callback`
- Behaviour:
  - If a user with that email already exists, the Google account is
    linked (`google_id` and `google_token` updated; if
    `email_verified_at` is null it is set to `now()`)
  - Otherwise a new user is created with `email_verified_at = now()`
    (Google emails are pre-verified) and `password` left null
- Required env vars: `GOOGLE_CLIENT_ID`, `GOOGLE_CLIENT_SECRET`,
  `GOOGLE_REDIRECT_URL`

#### 4.8.4 User Profile
- Profile page:
  - Edit name, email, phone number
  - Change password (optional; nullable for Google-only users)
  - Profile photo (upload, optional)
- "My Bookings" page:
  - List of all reservations (newest first, paginated)
  - Status: `Awaiting Payment`, `Paid`, `Confirmed`, `Completed`,
    `Cancelled`, `Expired`
  - Booking details (package info, date, guest count, total, e-ticket)
  - "Pay Now" for `pending`
  - "Download Invoice" for `paid` / `confirmed` / `completed`
  - "Write Review" for `completed` without a review yet

---

### 4.9 Admin Panel (Filament PHP)

Built using **Filament PHP 3.3** with the following features:

#### 4.9.1 Dashboard
- Stats overview:
  - Bookings today (description shows monthly total)
  - Revenue this month (settled payments)
  - Registered users
  - **Total reviews** (auto-published; no pending counter)
- Latest bookings table widget
- Upcoming quota widget (next 7 days × all active packages)
- _(Roadmap §12)_ Booking trend chart, revenue chart

#### 4.9.2 Tour Package Management
- CRUD tour packages
- Fields: name, description, price, duration_hours, daily_capacity,
  facilities, multiple images (repeater), is_active, is_featured
- Slug auto-generated from name (`spatie/laravel-sluggable`)

#### 4.9.3 Reservation/Booking Management
- All bookings table with filters (status, date, package)
- Booking detail: account holder + guest data, package, date, guest
  count, total, payment status
- Edit form: change `status` (with future state-machine guard)
- _(Roadmap §12)_ Data export (CSV/Excel)

#### 4.9.4 Blog/Article Management
- CRUD articles with rich text editor
- Category management
- Image upload
- Status: Draft / Published
- SEO fields: meta title, meta description
- _(Roadmap §12)_ Tag management

#### 4.9.5 Review Management (read-only inspect + delete)
- Reviews table with searchable reviewer/package, rating column with
  star formatting, comment preview, created_at
- Form is **read-only** — admins inspect content but cannot toggle
  approve/reject (auto-publish policy, see §4.6)
- Available actions: **View detail**, **Delete** (spam/abuse only)
- Bulk delete supported

#### 4.9.6 Homepage Content Management
- Edit hero section (image, headline, sub-headline, CTA text)
- Edit "About Us" section (title, description, image grid)
- Edit "Education Documentation" section (photos, description, bullet
  points)
- Manage education documentation photo gallery

#### 4.9.7 User Management
- Registered users list, profile detail, booking history
- _(Roadmap §12)_ Block/unblock users

#### 4.9.8 Reports
- _(Roadmap §12)_ Visit reports by period
- _(Roadmap §12)_ Revenue reports by period
- _(Roadmap §12)_ Most popular tour package reports
- _(Roadmap §12)_ Export to CSV/PDF

---

## 5. Non-Functional Requirements

| Aspect | Requirement |
|--------|-------------|
| **Responsiveness** | Responsive on desktop, tablet, and mobile |
| **Performance** | Pages must load within < 3 seconds on a 3G connection |
| **Security** | CSRF, XSS prevention, input sanitization, bcrypt password hashing, Midtrans signature verification, signed email verification links |
| **SEO** | Meta tags, Open Graph, structured data, sitemap.xml _(Roadmap §12)_ |
| **Browser Support** | Chrome, Firefox, Safari, Edge (last 2 versions) |
| **Accessibility** | WCAG AA color contrast, alt text on images, keyboard navigable |
| **Language** | Dual-language UI (Indonesian & English) |
| **Availability** | 24/7 hosting with 99% uptime target |

---

## 6. Visual Design Guidelines

### 6.1 Theme & Atmosphere
- **Primary theme:** Nature / Agrotourism / Coffee
- **Atmosphere:** Premium, clean, modern, nature-inspired
- **Inspiration:** AYO.co.id (layout & UX), adapted with green
  nature/coffee color scheme

### 6.2 Color Palette

| Role | Color | Hex |
|------|-------|-----|
| Primary | Forest Green | `#1B4332` |
| Primary Light | Sage Green | `#2D6A4F` |
| Primary Lighter | Tea Green | `#52B788` |
| Accent | Coffee Brown | `#6F4E37` |
| Accent Light | Latte | `#C8A882` |
| Background | Off-white / Cream | `#FEFCF3` |
| Surface | White | `#FFFFFF` |
| Text Primary | Dark Charcoal | `#1A1A2E` |
| Text Secondary | Warm Gray | `#6B7280` |
| Danger / Error | Red | `#DC2626` |
| Success | Emerald | `#059669` |
| Warning | Amber | `#D97706` |

### 6.3 Typography
- **Headings:** `Playfair Display` or `Merriweather` (serif, elegant)
- **Body:** `Inter` or `Plus Jakarta Sans` (sans-serif, modern, readable)
- **Accent/Labels:** `Outfit` (clean, for buttons & labels)

### 6.4 UI Components
- **Border radius:** `8px` (cards), `24px` (rounded-full buttons)
- **Shadow:** Soft (`0 4px 6px -1px rgba(0,0,0,0.1)`)
- **Spacing:** 8px grid system
- **Animations:** Scroll-triggered fade-in, hover scale, smooth
  transitions (300ms ease)

---

## 7. Sitemap

```
/                              → Homepage
/paket-wisata                  → Tour Packages Listing
/paket-wisata/{slug}           → Tour Package Detail
/blog                          → Blog Listing
/blog/{slug}                   → Article Detail
/masuk                         → Login
/daftar                        → Registration
/auth/google/redirect          → Start Google OAuth
/auth/google/callback          → Google OAuth callback
/lupa-password                 → Forgot Password
/reset-password/{token}        → Reset Password Form
/email/verify                  → Verify Email notice (auth)
/email/verify/{id}/{hash}      → Email verification handler (auth + signed)
/profil                        → User Profile (auth + verified)
/booking                       → My Bookings (auth + verified)
/booking/create                → Review Order page (POST)
/booking                       → Create booking (POST)
/booking/{id}                  → Booking Detail & E-Ticket (auth + verified)
/booking/{id}/bayar            → Midtrans Checkout (auth + verified)
/booking/{id}/pay              → Snap token endpoint (POST)
/booking/{id}/update-status    → Client polling fallback (POST)
/booking/{id}/invoice          → Download Invoice PDF (auth + verified)
/booking/{id}/review           → Submit Review (POST)
/lang/{locale}                 → Switch language
/api/midtrans/notification     → Midtrans Webhook (server-to-server)
/api/kuota/{packageId}/{date}  → Real-time quota check (AJAX)
/admin/*                       → Filament Admin Panel
```

---

## 8. User Stories

### Tourist

| ID | As a | I want to | So that |
|----|------|-----------|---------|
| US-01 | Guest | See an informative homepage | I get a first impression of the agrotourism |
| US-02 | Guest | Browse tour packages listing | I can choose a suitable package |
| US-03 | Guest | See package details with quota availability | I know if my desired date is still available |
| US-04 | Guest | Register an account (email or Google) | I can make bookings quickly |
| US-04b | Guest | Verify my email after registering | The platform trusts my account before I book |
| US-05 | Tourist | Book a tour package online with a Review Order step | I can confirm guest details and accept terms |
| US-06 | Tourist | Pay via Midtrans | My payment is secure and auto-recorded |
| US-07 | Tourist | View my e-ticket and download an invoice PDF after payment | I have valid proof of booking and a printable receipt |
| US-08 | Tourist | Read blog posts about coffee | I can learn before visiting |
| US-09 | Tourist | See the location map | I know how to reach the location |
| US-10 | Tourist | Submit a review immediately after my visit is marked completed | I can share my experience without moderation delay |
| US-11 | Guest | Switch website language to English | Foreign tourists can read the information |
| US-12 | Tourist | View my booking history | I can track my reservation status |
| US-13 | Tourist | Re-check my payment status if the page seems stuck | I am not blocked by webhook delays |

### Admin

| ID | As an | I want to | So that |
|----|-------|-----------|---------|
| US-A1 | Admin | Manage tour packages | Package information is always up-to-date |
| US-A2 | Admin | View & manage incoming bookings | I can validate and organize schedules |
| US-A3 | Admin | Write & publish blog posts | Coffee and tourism info reaches a wider audience |
| US-A4 | Admin | Inspect and delete spam/abusive reviews | The platform stays civil while remaining transparent |
| US-A5 | Admin | Edit homepage content | The main page stays fresh and relevant |
| US-A6 | Admin | View visit & revenue summaries on the dashboard | I can analyze business performance at a glance |
| US-A7 | Admin | Monitor Midtrans payment statuses | I know which payments are settled or pending |

---

## 9. Assumptions & Constraints

### Assumptions
- CV Kopi Danau Atas has a Midtrans account (Sandbox for development,
  Production for live)
- A Google Cloud OAuth client is provisioned for the Sign-in-with-Google
  feature
- Admin has internet access and is capable of operating the Filament
  panel
- Tourists use modern browsers (Chrome, Firefox, Safari, Edge)
- An SMTP provider is configured for transactional emails (verification,
  password reset, booking confirmation)

### Constraints
- Internet connection in Alahan Panjang may be unstable → design must be
  lightweight
- Initial content (photos, package descriptions, coffee articles) must
  be prepared by the company
- Midtrans requires business verification for Production mode
- Google OAuth requires verified-domain configuration for production

---

## 10. Timeline (Waterfall)

| Phase | Duration | Period |
|-------|----------|--------|
| Requirement Analysis | 2 weeks | Weeks 1–2 |
| System Design (ERD, UML, UI/UX) | 3 weeks | Weeks 3–5 |
| Implementation (Coding) | 6 weeks | Weeks 6–11 |
| Testing (Black Box) | 2 weeks | Weeks 12–13 |
| Deployment & Maintenance | 1 week | Week 14 |

---

## 11. Acceptance Criteria

1. ✅ Tourists can make reservations and pay through the website without
   manual intervention
2. ✅ System rejects bookings when daily quota is full (0 double-booking)
3. ✅ Admin can manage all v2.0 content through the Filament panel
4. ✅ Midtrans payment works end-to-end (Sandbox), including the polling
   fallback for delayed webhooks
5. ✅ Website is responsive and accessible on mobile devices
6. ✅ Blog can be updated by admin
7. ✅ Visitor reviews are auto-published; admins can delete spam
8. ✅ Google Maps is integrated on the homepage
9. ✅ Indonesian/English language toggle works
10. ✅ Email verification gate enforced before booking
11. ✅ Google OAuth login works end-to-end (Sandbox)
12. ✅ Booking confirmation emails are queued and delivered
13. ✅ Invoice PDF can be downloaded for paid bookings
14. ✅ All v2.0 features pass Black Box Testing

---

## 12. Roadmap (post-v2.0)

The following items were originally listed in v1.0 but are deferred to a
future release. They remain in the PRD as a reference for product
direction.

| Roadmap Item | Source | Notes |
|---|---|---|
| Tour package filters (price, duration, availability) | §4.3.1 | Requires query-builder UI + index review |
| Tour package sort (price, popularity, rating) | §4.3.1 | Pairs with filters |
| Documentation carousel `01 / 03` on homepage | §4.2 §4 | Currently a static grid |
| Blog **tag** management (multi-tag per post) | §4.5 / §4.9.4 | Categories already supported |
| Coffee variety **public** catalog page | §3.1 | Admin-managed today, no public route |
| Block/unblock users | §4.9.7 | Requires `users.is_blocked` column + login guard |
| Booking export (CSV / Excel) | §4.9.3 | |
| Booking trend chart + revenue chart widgets | §4.9.1 | Stats widget already exists |
| Reports page (visit / revenue / top packages, PDF export) | §4.9.8 | |
| Sitemap.xml + Open Graph + JSON-LD structured data | §5 | |
| Lighthouse Performance ≥ 80 / Accessibility ≥ 90 | §5 | Requires lazy-load, alt-text audit |
