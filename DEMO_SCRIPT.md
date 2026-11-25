# Five-Minute Video Demonstration Script & Presenter Screenplay
## Project: AI Builds My Website (Technical Internship Assignment for Websites.co.in)

This document provides a comprehensive, second-by-second screenplay and presenter guide designed to showcase the complete application workflow within a strict **5-minute (300-second)** recording window on Loom, OBS Studio, or Google Drive.

---

## 1. Video Overview & Demonstration Targets

- **Target Duration**: Exactly 4 minutes 45 seconds to 5 minutes 00 seconds (Max 300 seconds).
- **Core Theme**: "Zero Invention of Facts" — How our multi-source ingestion, contradiction detection, and fact-grounded Gemini AI engine construct authentic business websites without hallucinated claims.
- **Demonstrated Real Businesses**:
  1. **Patil Traditional Spices & Foods** (Shahupuri, Kolhapur, Maharashtra) — Retail / Food Manufacturing. Live at: `/sites/patil-traditional-spices`
  2. **Dr. Kulkarni Ayurvedic Wellness Clinic** (Kothrud, Pune, Maharashtra) — Professional Healthcare. Live at: `/sites/dr-kulkarni-ayurveda`
- **Universal Demo Credentials**:
  * Email: `demo@websites.co.in`
  * Password: `password123`
  * One-Click Seeder: `php artisan demo:populate`

---

## 2. Master Demonstration Timeline (300 Seconds)

```
0:00 ─── 0:40 │ Part 1: Problem Statement, Core Architecture & SaaS Dashboard (40s)
0:40 ─── 1:25 │ Part 2: Business Profiles, Multi-Format Ingestion & SSRF Defense (45s)
1:25 ─── 2:15 │ Part 3: Fact Extraction, Verification & Conflict Resolution Engine (50s)
2:15 ─── 3:05 │ Part 4: Grounded Gemini AI Generation & Responsive Preview Canvas (50s)
3:05 ─── 4:00 │ Part 5: Visual Editor, Side-by-Side AI Regeneration & Version History (55s)
4:00 ─── 4:40 │ Part 6: Live Public Publishing, SEO Audit & Incognito Verification (40s)
4:40 ─── 5:00 │ Part 7: Engineering Highlights, 159 Passing Tests & Wrap-Up (20s)
```

---

## 3. Second-by-Second Presenter Screenplay

### ⏱️ 0:00 – 0:40 | Part 1: Problem Statement & Live Dashboard
- **On-Screen**: Browser opened to Landing Page (`http://127.0.0.1:8000/`) -> Click **"Sign In"** -> Enter `demo@websites.co.in` / `password123` with Alpine.js password visibility toggle -> Enter **Dashboard** (`http://127.0.0.1:8000/dashboard`).
- **Presenter Actions**:
  1. Show clean SaaS landing page with real environment pills (Laravel 11, PHP 8.4, MySQL 8.0, Google Gemini 2.5 Flash).
  2. Demonstrate the password toggle button (`data-testid="toggle-password"`).
  3. Land on Dashboard showing real database metric cards (Total Projects, Published Sites, Draft Sites, Extracted Facts).
- **Spoken Commentary**:
  > *"Hello Websites.co.in team! Welcome to **AI Builds My Website**. 
  > Generic AI website builders fail small businesses because they hallucinate fake phone numbers, non-existent addresses, and arbitrary pricing. Our platform is built on an unshakeable Golden Rule: **Never invent business facts**.
  > Every published claim must trace directly back to verified owner input, uploaded documents, or crawled public web sources.
  > Here on our dashboard, all counters reflect real-time MySQL database statistics, with active projects for both **Patil Traditional Spices** and **Dr. Kulkarni Ayurvedic Clinic**."*

---

### ⏱️ 0:40 – 1:25 | Part 2: Business Onboarding, Multi-Format Ingestion & SSRF Defense
- **On-Screen**: Navigate into *Patil Traditional Spices & Foods* -> Go to **Documents & Sources** (`/businesses/{id}/sources`).
- **Presenter Actions**:
  1. Show the uploaded catalog file `Patil_Spices_Catalog_2026.txt` (or PDF) with extracted text.
  2. Highlight that the system supports **PDFs** (via `smalot/pdfparser`), **DOCX** (via `ZipArchive`), and plain text.
  3. Show the web source crawler connected to `https://patilspices.example.com`.
  4. Explain that our outbound crawler implements multi-layer **SSRF defenses**, blocking private IP ranges (`10.0.0.0/8`, `192.168.0.0/16`), loopback (`127.0.0.1`), and cloud metadata (`169.254.169.254`) on all requests and redirect hops.
- **Spoken Commentary**:
  > *"We ingest authentic business data from multiple formats. Here under Documents & Sources, we uploaded Patil Spices' official catalog, extracting clean text automatically. 
  > When connecting online sources, our server-side crawler fetches public web data while strictly enforcing SSRF protection, preventing malicious access to internal networks or cloud metadata endpoints."*

---

### ⏱️ 1:25 – 2:15 | Part 3: Fact Extraction, Verification & Conflict Resolution Engine
- **On-Screen**: Navigate to **Verify Facts & Conflicts** (`/businesses/{id}/facts`).
- **Presenter Actions**:
  1. Scroll through extracted facts, showing verbatim supporting evidence excerpts and confidence scores.
  2. Highlight the **Contradiction Detection Engine**: point out a conflicting claim (e.g. differing phone numbers between an old flyer and current website).
  3. Click **"Resolve Conflict"**, select the confirmed accurate number (`+91 94220 54321`), and confirm it.
  4. Show that unresolved conflicts block website generation until owner-verified.
- **Spoken Commentary**:
  > *"Next is the heart of our auditability: the Fact Verification Center. Raw text is parsed into atomic business claims: phone, address, operating hours, and product lists.
  > Notice our automated contradiction engine: when two sources disagree—such as an outdated flyer versus an active website—the system flags them in red as conflicting. 
  > Instead of guessing, the engine blocks website generation until the business owner explicitly confirms the ground truth. Once confirmed, the status updates with full audit timestamps."*

---

### ⏱️ 2:15 – 3:05 | Part 4: Grounded Gemini AI Generation & Responsive Preview Canvas
- **On-Screen**: Click **"Generate Website"** (`/businesses/{id}/website/setup`).
- **Presenter Actions**:
  1. Show the pre-generation quality checklist (Zero blocking conflicts, verified facts audit).
  2. Select style preset: **Premium Elegant** (`Playfair Display` serif, warm saffron `#b45309`, deep charcoal `#1c1917`).
  3. Select included sections (Hero, About, Products, Features, Testimonials, Location, Contact, Footer).
  4. Click **"Generate Website with Gemini AI"**. Show the animated loading overlay (`data-testid="generating-overlay"`).
  5. Website is generated and displayed on the interactive canvas. Toggle between **Desktop**, **Tablet (768px)**, and **Mobile (375px)** device bezels.
- **Spoken Commentary**:
  > *"With all facts verified, we proceed to generation. The pre-flight audit confirms zero blocking conflicts. We select the Premium Elegant preset tailored for artisanal heritage and click Generate.
  > Google Gemini 2.5 Flash synthesizes responsive sections using low temperature and strict prompt-injection barriers. If the external AI API is ever unreachable, our deterministic fallback engine guarantees zero downtime. 
  > In the preview canvas, we can instantaneously switch between Desktop, Tablet 768px, and Mobile 375px viewports."*

---

### ⏱️ 3:05 – 4:00 | Part 5: Visual Editor, Side-by-Side AI Regeneration & Version History
- **On-Screen**: In the Visual Website Editor (`/websites/{id}/edit`).
- **Presenter Actions**:
  1. Edit a section title or heading directly in the left sidebar; show save indicator changing from `"Saving changes..."` with spinner to `"All changes saved"`.
  2. Click **"Regenerate"** on the About section with creative prompt: *"Highlight our 40-year heritage of iron kadhai roasting in Shahupuri, Kolhapur"*.
  3. Highlight the **Side-by-Side Diff Modal** showing Original vs. Proposed text.
  4. Click **"Apply Regeneration"**. Show version counter incrementing to `v2`.
  5. Click **"Versions"** to show historical snapshots and one-click rollback capability.
- **Spoken Commentary**:
  > *"Inside the visual editor, owners have complete creative freedom. Every edit triggers real-time saving with clear status indicators.
  > If we want fresh copy, we can regenerate an individual section using Gemini AI. Notice our side-by-side diff modal: the owner can compare the original and proposed copy before applying it.
  > Every applied change creates an immutable snapshot in our version history, allowing instant rollbacks."*

---

### ⏱️ 4:00 – 4:40 | Part 6: Live Public Publishing, SEO Audit & Incognito Verification
- **On-Screen**: Click **"Publish Live"** -> Open live URL `http://127.0.0.1:8000/sites/patil-traditional-spices` in a new Incognito / Private window.
- **Presenter Actions**:
  1. Show public website loading instantly without login requirement.
  2. Scroll through sections: Hero badge, verified Kolhapuri Kanda Lasun Masala with real pricing, physical address `45 Shahupuri 2nd Lane`, phone `+91 94220 54321`.
  3. Briefly open second demo business: `http://127.0.0.1:8000/sites/dr-kulkarni-ayurveda` in Modern Minimal green theme, showcasing Nadi Pariksha and Panchakarma therapies in Pune.
  4. View page source to demonstrate `<title>`, `<meta name="robots" content="index, follow">`, canonical link, and Open Graph tags.
- **Spoken Commentary**:
  > *"Now we publish live. The website is published at its unique vanity slug: /sites/patil-traditional-spices. 
  > Opening the link in an incognito window without logging in, visitors see a blazing-fast, server-rendered website. Every single detail—the phone number, address, FSSAI number, and price list—is 100% grounded in verified data.
  > Here is our second live business: Dr. Kulkarni Ayurvedic Clinic in Pune, beautifully rendered in emerald green with verified clinical therapies and registration numbers. Both sites feature complete Open Graph tags and canonical URLs for search engine indexing."*

---

### ⏱️ 4:40 – 5:00 | Part 7: Engineering Highlights & Wrap-Up
- **On-Screen**: Terminal showing `php artisan test` results (159 passing tests) and repository overview.
- **Presenter Actions**:
  1. Point out the test suite: **159 automated tests with 934 assertions (100% passing)**.
  2. Highlight security defenses: CSRF, XSS escaping, authentication throttling (5 attempts/min), IDOR prevention across all models, and daily MySQL backup scripts (`scripts/backup-db.sh`).
  3. Thank the Websites.co.in review team.
- **Spoken Commentary**:
  > *"Under the hood, the entire application is defended against CSRF, stored XSS, IDOR, and brute-force attacks. Our test suite includes **159 automated tests with 934 assertions**, all passing green. Complete deployment manuals, database schemas, and API documentation are available in the repository.
  > Thank you to the Websites.co.in hiring team for this incredible opportunity!"*

---

## 4. Presenter Recording Checklist

### Technical Preparation:
- [ ] **Display Resolution**: Set screen resolution to **1920x1080 (1080p, 16:9)**.
- [ ] **Browser Zoom**: Set browser zoom to **100%** or **110%** for crystal-clear readability.
- [ ] **Pre-Launch Server**: Confirm `php artisan serve --host=127.0.0.1 --port=8000` is active.
- [ ] **Pre-Populate Data**: Run `php artisan demo:populate` before recording.
- [ ] **Browser Tabs Ready**:
  * Tab 1: `http://127.0.0.1:8000/` (Landing Page)
  * Tab 2: `http://127.0.0.1:8000/dashboard` (Logged in as `demo@websites.co.in`)
  * Tab 3: Incognito Window open and ready to paste `/sites/patil-traditional-spices`.
- [ ] **Audio Check**: Ensure microphone input is clear, with zero background noise.
- [ ] **Recording Tool**: Use **Loom** (preferred for instant shareable link) or **OBS Studio** (1080p 60fps recording uploaded to Google Drive / YouTube as Unlisted).

### Contingency Guidance:
* If Gemini API has a transient network timeout during live recording: The built-in deterministic fallback engine will construct the website seamlessly using confirmed facts—you can highlight this live as a demonstration of high-availability fault tolerance!
* Keep the pace steady and confident; do not rush through the conflict resolution or visual editor diff screens, as these showcase the core technical innovation of the assignment.
