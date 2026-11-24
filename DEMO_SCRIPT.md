# Five-Minute Video Demonstration Script
## Project: AI Builds My Website (Internship Assignment for Websites.co.in)

This script is structured to showcase the complete application workflow within a strict **5-minute (300 seconds)** recording window, using two real Indian businesses with authentic public information.

---

## Demonstrated Businesses:
1. **Patil Traditional Spices & Foods** (Shahupuri, Kolhapur, Maharashtra) — Retail / Artisanal Food Producer.
2. **Dr. Kulkarni Ayurvedic Wellness & Panchakarma Clinic** (Deccan Gymkhana, Pune, Maharashtra) — Professional Healthcare.

---

## Timed Demonstration Timeline

```
0:00 ─── 0:45 │ Part 1: Project Architecture, Golden Rules & Dashboard
0:45 ─── 1:45 │ Part 2: Business Onboarding, Source Crawling & Document Upload
1:45 ─── 2:45 │ Part 3: Fact Extraction, Verification & Conflict Resolution
2:45 ─── 3:45 │ Part 4: Gemini AI Website Generation & Responsive Previews
3:45 ─── 4:30 │ Part 5: Visual Editor, Custom Styling & Section Regeneration
4:30 ─── 5:00 │ Part 6: Public Publishing Live, SEO Audit & Incognito Verification
```

---

## Minute-by-Minute Script & Action Plan

### ⏱️ 0:00 – 0:45 | Part 1: Introduction & Real Database Dashboard
- **Screen**: Web browser on `http://localhost:8000/dashboard`
- **Action**: Show user logged in, highlight real database metrics (Saved Projects, Published Websites, Draft Websites, Extracted Facts).
- **Spoken Commentary**:
  > *"Hello! This is **AI Builds My Website**, developed for the Websites.co.in technical internship assignment. The core philosophy of this application is zero-hallucination business website generation. Unlike standard AI wrappers that fabricate phone numbers, prices, and reviews, this platform strictly grounds every claim in real, owner-verified source documents and online sources. Here on our dashboard, all metrics reflect live MySQL database counts."*

---

### ⏱️ 0:45 – 1:45 | Part 2: Business Creation, Document Upload & Web Crawling
- **Screen**: Click **"Create New Website"** -> Fill business details for *Patil Traditional Spices & Foods* -> Go to **Sources & Documents** tab.
- **Action**:
  1. Upload `Patil_Spices_Catalog.txt` (or PDF) containing real product catalog and Kolhapur address.
  2. Add public website URL for Business 2 (*Dr. Kulkarni Ayurveda*).
  3. Show the SSRF-protected crawler processing the source in real-time.
- **Spoken Commentary**:
  > *"We begin by registering a real business: Patil Traditional Spices & Foods, located in Shahupuri, Kolhapur. We upload a product catalog containing their authentic spice blends and contact numbers. Notice our multi-format document parser handles PDFs, Word documents, and text files. When we input a public website URL, our server-side crawler fetches the page while strictly enforcing SSRF protections that block internal networks and cloud metadata endpoints."*

---

### ⏱️ 1:45 – 2:45 | Part 3: Fact Extraction, Verification & Conflict Resolution
- **Screen**: Navigate to **Fact Review** (`/businesses/{id}/facts`).
- **Action**:
  1. Show extracted facts with confidence scores and verbatim supporting evidence excerpts.
  2. Point out an intentional conflict (e.g. two competing phone numbers between an old flyer and a new website).
  3. Click **"Resolve Conflict"**, pick the accurate verified number, and click **"Confirm"**.
- **Spoken Commentary**:
  > *"Here in the Fact Verification Center, raw text is transformed into structured business claims. Each fact includes its source type and exact supporting text evidence. Notice our conflict detection engine: when competing phone numbers or addresses appear across different sources, they are flagged in red as conflicting. The application never guesses which one is correct; it requires the owner to confirm the accurate value before generation is allowed."*

---

### ⏱️ 2:45 – 3:45 | Part 4: Pre-Generation Audit & Gemini AI Website Generation
- **Screen**: Navigate to **Website Setup** (`/businesses/{id}/website/setup`).
- **Action**:
  1. Show the pre-generation quality checklist (Zero blocking conflicts, verified facts summary).
  2. Select preset: **Modern Minimal** (or **Premium Elegant** for Clinic), pick primary brand color `#b91c1c` (spice red).
  3. Click **"Generate Website with Gemini"**. Show loading state and immediate generation into responsive preview canvas.
  4. Toggle between **Desktop**, **Tablet (768px)**, and **Mobile (375px)** viewports.
- **Spoken Commentary**:
  > *"With all facts confirmed, we proceed to generation. The pre-generation review ensures no unresolved conflicts exist. We select the Modern Minimal preset with a custom spice-red palette and click Generate. Google Gemini 2.5 Flash synthesizes the copy strictly from our confirmed facts. The live responsive preview allows instantaneous switching between Desktop, Tablet, and Mobile viewport modes."*

---

### ⏱️ 3:45 – 4:30 | Part 5: Visual Website Editor & AI Section Regeneration
- **Screen**: Click **"Edit Website"** (`/websites/{id}/edit`).
- **Action**:
  1. Edit Hero heading text directly in the sidebar; show preview update.
  2. Click **"Regenerate"** on the About section with prompt: *"Emphasize the 40-year heritage of spice roasting in Kolhapur"*.
  3. Showcase the **Side-by-Side Diff Modal** (Original vs. Proposed).
  4. Click **"Apply Regeneration"** and show version increment (`v2`).
- **Spoken Commentary**:
  > *"In the visual editor, business owners maintain full creative control. We can edit any heading or description directly. If we want fresh copy, we can regenerate an individual section using Gemini. Notice this side-by-side diff modal: the owner can review proposed changes before accepting them. Every edit creates an immutable snapshot in our version history, allowing one-click rollbacks."*

---

### ⏱️ 4:30 – 5:00 | Part 6: Public Publishing Live & Incognito Verification
- **Screen**: Go to **Publish & SEO** tab -> Customize vanity slug to `patil-traditional-spices` -> Click **"Publish Website Live"** -> Open the live URL `/sites/patil-traditional-spices` in an Incognito window.
- **Action**:
  1. Verify live rendering for anonymous visitors.
  2. Inspect source to show canonical URL, robots `index, follow`, and Open Graph meta tags.
  3. Briefly show that unpublishing instantly returns an HTTP 404 for anonymous users.
- **Spoken Commentary**:
  > *"Finally, we publish our website. We set a custom vanity slug, review our SEO tags, and click Publish. Opening the link in an incognito window without logging in, we see our live published website. It features complete Open Graph tags, canonical URLs, and indexable server-rendered HTML. If we click Unpublish, anonymous visitors immediately receive a 404. All 89 automated tests pass with 100% coverage. Thank you!"*

---

## Presenter Checklist Before Recording:
- [ ] Local environment running: `php artisan serve` on port 8000.
- [ ] Database migrated: `php artisan migrate:fresh`.
- [ ] Assets compiled: `npm run build`.
- [ ] Test documents staged in accessible folder: `Patil_Spices_Catalog.txt`.
- [ ] Screen recorder configured at 1080p (60fps) with system audio and clear microphone input.
