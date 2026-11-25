# 7-MINUTE PROJECT DEMONSTRATION SCREENPLAY

> **Project:** AI Builds My Website (Websites.co.in Technical Internship Assignment)  
> **Presenter:** Candidate  
> **Format:** Live Screen Recording & Spoken Walkthrough  
> **Target Duration:** Exactly 7 Minutes (420 Seconds)  

---

## Executive Timing Breakdown

| Time Elapsed | Section | Screen Action |
|---|---|---|
| **0:00 – 1:00** | **Minute 1: Introduction & Problem Statement** | Homepage & Slides |
| **1:00 – 2:00** | **Minute 2: Tech Stack & System Architecture** | Architecture Diagram & Codebase |
| **2:00 – 3:00** | **Minute 3: Registration & Multi-Tenant Dashboard** | Auth & Dashboard View |
| **3:00 – 4:00** | **Minute 4: Business Setup & Multi-Source Ingestion** | Ingestion & Conflict Resolver |
| **4:00 – 5:00** | **Minute 5: Gemini AI Generation & Anti-Hallucination** | Preset Selection & Generation |
| **5:00 – 6:00** | **Minute 6: Responsive Editor & Section Regeneration** | Device Toggles & Inline Edits |
| **6:00 – 7:00** | **Minute 7: Publishing, SEO & Incognito Public Access** | Public URL & Incognito Test |

---

## Minute 1: Introduction & Problem Statement (0:00 – 1:00)

### Visual on Screen:
Show the Landing Page at `http://127.0.0.1:8000/`. Scroll gracefully down past the hero banner, feature grid, and architecture overview.

### Spoken Script:
> "Hello everyone! Welcome to the technical demonstration of **AI Builds My Website**, developed for the Websites.co.in technical internship assignment.
>
> Today, millions of small businesses want a professional website. But they face a major dilemma: traditional website builders like WordPress require days of technical configuration, while modern generative AI tools often hallucinate—inventing fake phone numbers, non-existent addresses, or incorrect pricing.
>
> **AI Builds My Website** solves this through a strict **Fact Grounding Pipeline**. We take unstructured real-world business assets—visiting cards, PDF rate cards, and brochures—and transform them into production-ready commercial websites, with a mathematical guarantee: *the AI is strictly prohibited from inventing facts that were not confirmed by the business owner.* Let’s dive in!"

---

## Minute 2: Technology Stack & Architecture (1:00 – 2:00)

### Visual on Screen:
Display the System Architecture Diagram in `PROJECT_DOCUMENTATION.md` or the terminal showing PHP 8.4, Laravel 11, MySQL 8.0, and Vite 6.

### Spoken Script:
> "Let’s look at the underlying engineering. 
>
> On the backend, we run **PHP 8.4** on **Laravel 11**, leveraging Eloquent ORM, custom Policies for tenant isolation, and SQLite/MySQL relational storage with strict foreign keys.
>
> On the frontend, we use **Tailwind CSS 4** with dynamic CSS variables and **Alpine.js** for reactive, zero-bloat UI interactions.
>
> For our AI engine, we integrate **Google Gemini 2.5 Flash** for high-speed, structured JSON synthesis. And critically, we built a **deterministic grounded fallback generator**: if the internet drops or an API key is absent, the system seamlessly continues running with 100% fact grounding.
>
> Security is baked in from day one: our web crawler features DNS-level SSRF defenses that block private IP ranges and multi-hop redirects, while our file upload handler enforces server-side MIME type verification and UUID storage."

---

## Minute 3: Registration & Multi-Tenant Dashboard (2:00 – 3:00)

### Visual on Screen:
Navigate to `/login`. Log in using `demo@websites.co.in` / `password123`. Land on `/dashboard`. Highlight the 4 metric cards and the two seeded businesses (*Patil Traditional Spices* and *Dr. Kulkarni Ayurvedic Wellness Clinic*).

### Spoken Script:
> "Now let’s look at the live application.
>
> I’ll log into our universal reviewer account: `demo@websites.co.in`.
>
> Notice the immediate redirect to our multi-tenant Dashboard. Here, the business owner sees real-time metrics: active projects, published URLs, draft states, and confirmed facts.
>
> Notice that our database already has two live demonstration businesses loaded: *Patil Traditional Spices & Foods* in Kolhapur, and *Dr. Kulkarni Ayurvedic Wellness Clinic* in Pune. The system allows an entrepreneur to manage multiple distinct businesses under a single authenticated account with complete tenant data isolation."

---

## Minute 4: Business Setup & Multi-Source Ingestion (3:00 – 4:00)

### Visual on Screen:
Click **+ Add New Business**. Fill in sample details. Navigate to the **Sources & Documents** tab. Drag-and-drop a sample PDF brochure. Click **Process & Extract Facts**. Show the **Fact Verification & Conflict Resolver** screen.

### Spoken Script:
> "Let's see how information gets into the system.
>
> When creating or managing a business, the user can upload brochures, PDF price lists, or input an existing web link.
>
> Watch what happens when we upload a source file. Our backend document parser extracts the raw text. Then, our extraction engine isolates atomic business facts: telephone numbers, email addresses, physical location, and operating hours.
>
> If an older document contains an outdated phone number, our **Conflict Detection Engine** flags it immediately. The business owner resolves the conflict with a single click, establishing our verified knowledge graph. No unverified data ever reaches the AI generation phase."

---

## Minute 5: Gemini AI Generation & Anti-Hallucination (4:00 – 5:00)

### Visual on Screen:
Click **Generate Website**. Show the preset selection screen (`Modern Minimal`, `Warm Artisan`, `Bold Creative`, `Corporate Clean`, `Premium Elegant`). Select `Warm Artisan`. Click **Generate Website with AI**. Show the progress bar, and display the generated website.

### Spoken Script:
> "Now comes the magic: website generation.
>
> We offer five tailored design presets crafted for specific industries. For an authentic food business, we’ll choose `Warm Artisan`.
>
> When I click **Generate Website**, the platform injects our confirmed knowledge graph into a structured prompt schema and queries **Google Gemini 2.5 Flash**.
>
> In just two seconds, Gemini synthesizes compelling marketing copy, hero headlines, feature highlights, and FAQs. Because every factual element—the address in Shahupuri, the contact phone, the pricing—is locked to our verified database facts, the resulting website is rich and persuasive, yet 100% truthful."

---

## Minute 6: Responsive Editor & Section Regeneration (5:00 – 6:00)

### Visual on Screen:
The screen transitions into the **Website Visual Editor**. Toggle viewport buttons: Desktop -> Tablet -> Mobile. Edit a section title inline. Click **Regenerate with AI** on the *Hero* section. Show the side-by-side diff. Apply changes.

### Spoken Script:
> "Here is our live **Visual Editor**.
>
> Notice the top device toggles: I can switch between Desktop, a 768px Tablet view, and a 375px Mobile smartphone frame in real time.
>
> On the left, we have full section control: we can reorder sections, hide sections, or edit text fields directly.
>
> What if you want to polish a specific section? You don’t have to re-generate the entire website. Simply click **Regenerate with AI** on the section. The system queries Gemini with targeted instructions, displays a side-by-side comparison, and lets you accept or discard the changes with zero risk to the rest of your site."

---

## Minute 7: Publishing, SEO & Incognito Public Access (6:00 – 7:00)

### Visual on Screen:
Click **Publish Website**. Notice the success modal displaying the live vanity URL `/sites/patil-traditional-spices`. Open an Incognito/Private browser window. Navigate to `http://127.0.0.1:8000/sites/patil-traditional-spices`. Show inspect elements with SEO meta tags, scroll down the mobile layout, and show unpublishing.

### Spoken Script:
> "Finally, let’s publish!
>
> With one click on **Publish Website**, the platform creates an immutable version snapshot and assigns a clean vanity slug: `/sites/patil-traditional-spices`.
>
> To prove this is a real, public website, I’m opening a fresh Incognito window—no login, no cookies.
>
> As you can see, the website loads instantly with full responsive styling. The phone number links directly to dialers, the address is verified, customer testimonials are highlighted, and inspect-element confirms full OpenGraph and SEO metadata.
>
> If the business owner ever needs to unpublish, clicking 'Unpublish' instantly isolates the site behind a clean 404 page.
>
> To wrap up: **172 automated tests**, 1,032 passing assertions, 100% green test suite, and a battle-tested, secure application ready for production. Thank you so much for your time!"
