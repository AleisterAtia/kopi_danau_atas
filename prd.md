# PRD — E-Tourism Information System for CV Kopi Danau Atas

> **Version:** 1.0  
> **Date:** May 2, 2026  
> **Author:** Fadhil Dzaky Arhab — 2301092010  
> **Thesis Title:** Design and Development of a Web-Based E-Tourism Information System for CV Kopi Danau Atas

---

## 1. Executive Summary

CV Kopi Danau Atas is an Arabica Solok coffee processing and trading company located in the Danau Diatas highland area, Alahan Panjang. The company holds significant agrotourism potential, yet still manages reservations manually via notebooks and WhatsApp — leading to double-booking incidents and limited tourist information availability.

This system is an **integrated E-Tourism website** that digitizes the entire tour package reservation process, presents a coffee education catalog, integrates location maps, and provides a visitor review system — powered by **Midtrans payment gateway** for automated payments.

---

## 2. Product Objectives

| # | Objective | Success Metric |
|---|-----------|----------------|
| 1 | Eliminate double-booking | 0% schedule overlap incidents |
| 2 | End-to-end reservation digitization | 100% bookings through the website |
| 3 | Automated payment via Midtrans | Real-time payment verification without manual intervention |
| 4 | Enhance tourist information | Visitors receive comprehensive info (maps, blog, coffee catalog) before visiting |
| 5 | Collect visitor feedback | Integrated review & rating system |

---

## 3. Users & Roles (Actors)

### 3.1 Guest (Non-Authenticated Visitor)
- Browse the homepage, tour packages, blog, and coffee catalog
- View location map and visitor reviews
- Switch language (Indonesian ↔ English)
- Access login and registration pages

### 3.2 Registered Tourist (Authenticated User)
- All Guest access rights
- Book/reserve tour packages
- Make payments via Midtrans (Snap)
- View booking history & payment status
- Submit reviews and ratings after visits
- Manage account profile

### 3.3 Admin (CV Kopi Danau Atas Manager)
- Filament PHP dashboard access
- Manage tour packages (CRUD + daily quota)
- Validate/approve/reject bookings
- Manage blog/articles (coffee journals, tourism information)
- Manage homepage content (hero image, descriptions, information sections)
- Manage coffee variety catalog
- Moderate visitor reviews
- View visit reports & payment transactions
- Manage education/documentation photo gallery

---

## 4. Features & Functional Specifications

### 4.1 Navbar (Header Navigation)

The navbar is always visible across all public pages with a fixed/sticky layout.

| Element | Position | Description |
|---------|----------|-------------|
| **Logo** | Left | CV Kopi Danau Atas logo, click → homepage |
| **Beranda** (Home) | Main menu | Link to the homepage |
| **Paket Wisata** (Tour Packages) | Main menu | Link to tour packages listing |
| **Blog** | Main menu | Link to blog/coffee journal page |
| **Masuk** (Sign In) | Right | Text link button for login |
| **Daftar** (Sign Up) | Right | Primary filled button for registration |
| **🌐 ID / EN** | Far right | Dropdown/toggle for language switching |

> **Design Reference:** Similar to the AYO.co.id navbar — clean, minimalist, with a "Daftar" button in accent color (dark green / forest green matching the coffee theme).

---

### 4.2 Homepage (Landing Page)

The main page consists of multiple vertically flowing sections:

#### Section 1: Hero Banner
- **Background:** Full-width, high-resolution photo of the coffee plantation / Danau Diatas scenery
- **Overlay:** Semi-transparent dark gradient
- **Headline:** Large, bold text — e.g., *"Explore the Beauty of Solok Coffee Agrotourism"*
- **Sub-headline:** Brief 1–2 sentence description
- **CTA Button:** "Book Now" → navigates to tour packages page
- **All hero text & images are editable by admin** via the Filament panel

#### Section 2: About Us / Brief Information
- Layout: Text on the left + image grid on the right (similar to AYO's second screenshot)
- **Section title** (admin-editable)
- **Description paragraph** (admin-editable)
- **Image grid** (3–5 photos of the plantation, coffee process, etc. — managed by admin)
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
- Layout: Large photo/mockup on the left + content on the right (similar to AYO's third screenshot)
- Displays **documentation photos** of tourists performing educational activities (coffee picking, roasting, cupping)
- Title: *"Coffee Education Experience"* (editable)
- Description of available educational activities
- Feature bullet points with green checkmark icons
- Carousel/slider navigation: `01 / 03` with left-right arrows

#### Section 5: Visitor Reviews (Testimonials)
- Layout: Dark green/themed card on the left with *"What Our Visitors Say"* title + review card on the right (similar to AYO's fifth screenshot)
- Displays visitor reviews:
  - **Profile photo**
  - **Name**
  - **City of origin**
  - **Review text** (quote)
  - **Star rating** (1–5)
- Carousel navigation: `01 / 05` with arrows
- Only displays admin-moderated reviews

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
  - Package name (e.g., "Coffee Picking Package", "Roasting Experience Package")
  - Duration (e.g., "3 hours")
  - Price per person
  - Daily capacity/quota
  - Remaining slots today (real-time)
  - Average rating
  - "Book" / "Details" button
- Filters: Price range, duration, availability
- Sort: Price, popularity, rating

#### 4.3.2 Package Detail Page
- Photo gallery (carousel)
- Package name, full description
- Detailed information:
  - Price per person
  - Activity duration
  - Capacity per session
  - Available schedule (calendar view)
  - Included facilities (list)
  - Important notes
- Booking Form:
  - Select visit date (date picker, minimum: D+1)
  - Number of guests
  - Real-time validation: displays remaining quota on the selected date
  - If quota is full → disable and show "Quota full for this date" message
  - "Proceed to Payment" button
- Visitor reviews section for this package

---

### 4.4 Reservation & Payment System

#### 4.4.1 Booking Flow (User Flow)

```
Select Package → Choose Date & Guest Count → Review Order
→ Checkout (Midtrans Snap) → Payment → Auto-Confirmation
→ E-Ticket (unique code) → Email Notification
```

#### 4.4.2 Midtrans Payment Gateway Integration

| Aspect | Detail |
|--------|--------|
| **Integration method** | Midtrans Snap (popup/redirect) |
| **Payment methods** | Bank Transfer (BCA, BNI, BRI, Mandiri), GoPay, ShopeePay, QRIS, Credit/Debit Card |
| **Payment flow** | Client → Laravel Backend (create transaction) → Midtrans API → Snap Token → Client Snap UI → Payment → Webhook Notification → Update Status |
| **Webhook / Notification** | Endpoint receives Midtrans callbacks for automatic status updates |
| **Transaction statuses** | `pending` → `paid` → `confirmed` / `expired` / `cancelled` / `refunded` |
| **Security** | Signature key verification on every webhook |

#### 4.4.3 Quota Management

- Each package has a **daily capacity quota** (e.g., 20 persons/day)
- When a user selects a date, the system calculates:
  ```
  Remaining Quota = Daily Capacity - SUM(guest_count of bookings with status paid/confirmed on that date)
  ```
- If requested guest count > remaining quota → reject with error message
- Bookings with `pending` status for more than 1 hour → automatically expired (quota freed)

#### 4.4.4 E-Ticket

- After successful payment (status `paid`), the system generates:
  - **Unique booking code** (format: `KDA-YYYYMMDD-XXXXX`)
  - **QR Code** containing the booking code
- E-Ticket accessible on the "My Bookings" page
- Also sent via **email** (optional)

---

### 4.5 Blog Page

- Blog/article listing page (grid layout)
- Each card contains:
  - Thumbnail image
  - Article title
  - Publish date
  - Category (e.g., "Coffee Varieties", "Travel Tips", "News")
  - Excerpt (2–3 lines)
  - "Read More" button
- Article detail page:
  - Title, date, category, author
  - Article content (rich text / markdown)
  - Supporting images
  - Share buttons (WhatsApp, Facebook, Twitter)
  - Related articles below
- **Admin** manages blog via Filament:
  - CRUD articles
  - Rich text editor (WYSIWYG)
  - Image upload
  - Category and tag management
  - Status control: draft / published

> The blog covers **coffee-related journals/information** (Lini-S, Gayo, Typica varieties), visit tips, plantation news, and other educational content.

---

### 4.6 Review & Rating System

- Registered tourists who have **completed their visit** (booking status = `completed`) can submit reviews
- Review form:
  - Star rating (1–5, required)
  - Text comment (min 10 characters, required)
  - Photo upload (optional, max 3 photos)
- Reviews are displayed after **admin moderation**
  - Review status: `pending` → `approved` / `rejected`
- Approved reviews appear on:
  - Testimonials section on the homepage
  - Related tour package detail page

---

### 4.7 Multi-Language (Internationalization)

- **Default language:** Indonesian (ID)
- **Secondary language:** English (EN)
- Language toggle on navbar (globe icon + dropdown)
- Scope of translation:
  - Static UI labels (navbar, buttons, placeholders, footer, etc.)
  - Dynamic content (tour packages, blog, homepage descriptions) → **only UI labels are translated**, content remains in the language entered by admin
- Implementation: Laravel Localization (`lang/id.json` & `lang/en.json`)

---

### 4.8 Authentication & Account Management

#### 4.8.1 Registration (Sign Up)
- Fields: Full name, Email, Password, Confirm Password
- Unique email validation
- After registration → auto-login

#### 4.8.2 Login (Sign In)
- Fields: Email, Password
- "Forgot Password" button → reset via email

#### 4.8.3 User Profile
- Profile page:
  - Edit name, email, phone number
  - Change password
  - Profile photo (upload)
- "My Bookings" page:
  - List of all reservations (newest first)
  - Status: `Awaiting Payment`, `Paid`, `Confirmed`, `Completed`, `Cancelled`, `Expired`
  - Booking details (package info, date, guest count, total, e-ticket)
  - "Pay Now" button for `pending` bookings
  - "Write Review" button for `completed` bookings

---

### 4.9 Admin Panel (Filament PHP)

Admin dashboard built using **Filament PHP 3.3** with the following features:

#### 4.9.1 Dashboard
- Summary statistics:
  - Total bookings today / this week / this month
  - Total revenue this month
  - Number of registered visitors
  - Number of pending reviews
- Visit trend chart (line chart)
- Latest bookings (table)

#### 4.9.2 Tour Package Management
- CRUD tour packages
- Fields: Name, description, price, duration, daily capacity, facilities, images (multiple), status (active/inactive), is_featured (shown on homepage)
- Multiple image upload
- Preview & bulk actions

#### 4.9.3 Reservation/Booking Management
- All bookings table with filters (status, date, package)
- Booking detail: booker data, package, date, guest count, total, payment status
- Actions: Approve, Reject, Mark as Completed
- Data export (CSV/Excel)

#### 4.9.4 Blog/Article Management
- CRUD articles with rich text editor
- Category & tag management
- Image upload
- Status: Draft / Published
- SEO fields: meta title, meta description

#### 4.9.5 Review Management
- Reviews table with filters (status, rating, package)
- Actions: Approve / Reject
- View detail (text + photos)

#### 4.9.6 Homepage Content Management
- Edit hero section (image, headline, sub-headline, CTA text)
- Edit "About Us" section (title, description, image grid)
- Edit "Education Documentation" section (photos, description, bullet points)
- Manage education documentation photo gallery

#### 4.9.7 User Management
- Registered users list
- Profile details & booking history
- Block/unblock users

#### 4.9.8 Reports
- Visit reports by period
- Revenue reports by period
- Most popular tour package reports
- Export to CSV/PDF

---

## 5. Non-Functional Requirements

| Aspect | Requirement |
|--------|-------------|
| **Responsiveness** | Website must be responsive on desktop, tablet, and mobile (smartphone) |
| **Performance** | Pages must load within < 3 seconds on a 3G connection |
| **Security** | CSRF protection, XSS prevention, input sanitization, password hashing (bcrypt), Midtrans signature verification |
| **SEO** | Meta tags, Open Graph, structured data, sitemap.xml |
| **Browser Support** | Chrome, Firefox, Safari, Edge (last 2 versions) |
| **Accessibility** | WCAG AA color contrast, alt text on images, keyboard navigable |
| **Language** | Dual-language UI (Indonesian & English) |
| **Availability** | 24/7 hosting with 99% uptime target |

---

## 6. Visual Design Guidelines

### 6.1 Theme & Atmosphere
- **Primary theme:** Nature / Agrotourism / Coffee
- **Atmosphere:** Premium, clean, modern, nature-inspired
- **Inspiration:** AYO.co.id (layout & UX), adapted with green nature/coffee color scheme

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
- **Border radius:** `8px` (rounded-lg) for cards, `24px` (rounded-full) for buttons
- **Shadow:** Soft shadow (`0 4px 6px -1px rgba(0,0,0,0.1)`)
- **Spacing:** Consistent 8px grid system
- **Animations:** Scroll-triggered fade-in, hover scale, smooth transitions (300ms ease)

---

## 7. Sitemap

```
/                           → Homepage (Landing Page)
/paket-wisata               → Tour Packages Listing
/paket-wisata/{slug}        → Tour Package Detail
/blog                       → Blog/Article Listing
/blog/{slug}                → Article Detail
/masuk                      → Login Page
/daftar                     → Registration Page
/lupa-password              → Forgot Password Page
/profil                     → User Profile Page (auth)
/booking                    → My Bookings Page (auth)
/booking/{id}               → Booking Detail & E-Ticket (auth)
/booking/{id}/bayar         → Midtrans Checkout (auth)
/booking/{id}/ulasan        → Review Form (auth)
/api/midtrans/notification  → Midtrans Webhook (server-to-server)
/admin/*                    → Filament Admin Panel
```

---

## 8. User Stories

### Tourist

| ID | As a | I want to | So that |
|----|------|-----------|---------|
| US-01 | Guest | See an informative homepage | I get a first impression of the agrotourism |
| US-02 | Guest | Browse tour packages listing | I can choose a suitable package |
| US-03 | Guest | See package details with quota availability | I know if my desired date is still available |
| US-04 | Guest | Register an account | I can make bookings |
| US-05 | Tourist | Book a tour package online | I don't have to visit the location to reserve |
| US-06 | Tourist | Pay via Midtrans | My payment is secure and auto-recorded |
| US-07 | Tourist | View my e-ticket after payment | I have valid proof of booking |
| US-08 | Tourist | Read blog posts about coffee | I can learn before visiting |
| US-09 | Tourist | See the location map | I know how to reach the location |
| US-10 | Tourist | Submit a review after visiting | I can share my experience |
| US-11 | Guest | Switch website language to English | Foreign tourists can read the information |
| US-12 | Tourist | View my booking history | I can track my reservation status |

### Admin

| ID | As an | I want to | So that |
|----|-------|-----------|---------|
| US-13 | Admin | Manage tour packages | Package information is always up-to-date |
| US-14 | Admin | View & manage incoming bookings | I can validate and organize schedules |
| US-15 | Admin | Write & publish blog posts | Coffee and tourism info reaches a wider audience |
| US-16 | Admin | Moderate visitor reviews | Only appropriate reviews are displayed |
| US-17 | Admin | Edit homepage content | The main page stays fresh and relevant |
| US-18 | Admin | View visit & revenue reports | I can analyze business performance |
| US-19 | Admin | Monitor Midtrans payment statuses | I know which payments are settled or pending |

---

## 9. Assumptions & Constraints

### Assumptions
- CV Kopi Danau Atas has a Midtrans account (Sandbox for development, Production for live)
- Admin has internet access and is capable of operating the Filament panel
- Tourists use modern browsers (Chrome, Firefox, Safari, Edge)

### Constraints
- Internet connection in Alahan Panjang may be unstable → design must be lightweight
- Initial content (photos, package descriptions, coffee articles) must be prepared by the company
- Midtrans requires business verification for Production mode

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

1. ✅ Tourists can make reservations and pay through the website without manual intervention
2. ✅ System rejects bookings when daily quota is full (0 double-booking)
3. ✅ Admin can manage all content through the Filament panel
4. ✅ Midtrans payment works end-to-end (Sandbox)
5. ✅ Website is responsive and accessible on mobile devices
6. ✅ Blog & coffee catalog can be updated by admin
7. ✅ Visitor reviews are moderated before being displayed
8. ✅ Google Maps is integrated on the homepage
9. ✅ Indonesian/English language toggle works
10. ✅ All features pass Black Box Testing
