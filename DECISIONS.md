# Architectural Decision Records (DECISIONS.md)
## AI BUILDS MY WEBSITE — Engineering & Design Rationale

This document captures the technical decisions, architectural trade-offs, and design principles implemented in **AI Builds My Website**. It explains **why** specific technical paths were chosen over alternatives.

---

## 1. Framework & Database Selection: Laravel 11 + MySQL 8.0

### Decision:
Use **Laravel 11** with **MySQL 8.0** as the monolithic full-stack application foundation.

### Rationale:
1. **Batteries-Included Security & Velocity**: Laravel provides built-in CSRF verification, session handling, Bcrypt password hashing, rate limiting, and an expressive Policy authorization layer. In an internship assignment with strict security requirements, using an established framework prevents vulnerabilities common in custom or fragmented setups.
2. **Relational Integrity for Multi-Entity Data**: A website generation platform models complex relational dependencies: `User` -> `Business` -> `BusinessSource` / `UploadedFile` -> `ExtractedFact` -> `FactConfirmation` -> `Website` -> `WebsiteSection` -> `WebsiteVersion` -> `Publication`. MySQL 8.0 enforces foreign keys, cascading deletions, and transactional consistency (ACID), preventing orphaned data.
3. **Rich Service Architecture**: Laravel's service container allows cleanly decoupling controllers from heavy operations (e.g. `AiService`, `DocumentProcessingService`, `FactVerificationService`, `WebsitePublishingService`), facilitating isolated unit and feature testing.

### Alternatives Considered & Rejected:
- *Node.js / Express + MongoDB*: Rejected due to lack of enforced schema validation, manual foreign key management, and higher risk of data inconsistency when managing relational business facts and versions.
- *Python / Django*: Rejected due to Websites.co.in's primary tech stack alignment with PHP/Laravel and modern full-stack developer standards.

---

## 2. Rendering Strategy: Laravel Blade + Tailwind CSS + Alpine.js

### Decision:
Use server-rendered **Blade templates** enhanced with **Tailwind CSS 4** and **Alpine.js 3**, rather than a separate Single Page Application (SPA) like React or Next.js.

### Rationale:
1. **Optimal Public Website SEO**: Published business websites (`/sites/{slug}`) must be instantly indexable by search engines (Googlebot, Bingbot) and social media crawlers (WhatsApp, Facebook, Twitter). Server-side Blade rendering ensures complete `<title>`, `<meta description>`, Open Graph tags, and HTML content are present on initial HTTP response without relying on client-side JavaScript execution.
2. **Zero Hydration Latency**: Public business sites load instantly for end users on mobile devices with zero JavaScript bundle parsing overhead.
3. **Defensive XSS Protection by Default**: Blade automatically escapes all variable outputs (`{{ $var }}`) via `htmlspecialchars()`, mitigating stored and reflected Cross-Site Scripting.
4. **Lightweight Reactive Interactivity**: Alpine.js was selected for client-side state (editor split-view, device viewport toggles, modal dialogs) because it operates directly in the DOM without requiring a heavy virtual DOM runtime, state management store, or Node backend server.

### Alternatives Considered & Rejected:
- *Inertia.js + React/Vue*: Rejected because public business websites require static HTML delivery without the overhead of client-side React hydration for anonymous visitors.

---

## 3. Gemini AI Integration & Fact-Grounding Engine

### Decision:
Integrate **Google Gemini 2.5 Flash** with low sampling temperature (0.2), strict structured JSON output mode, prompt-injection defense boundaries, and an automated deterministic fallback engine.

### Rationale:
1. **The "Never Invent Facts" Golden Rule**: Generic LLMs hallucinate plausible-sounding phone numbers, business hours, and prices when unconstrained. We solved this by creating a **Confirmed Fact Dictionary** (`$verifiedDict`) that acts as the single source of truth. The prompt strictly instructs Gemini:
   > *"CRITICAL GOLDEN RULE: You MUST only use the confirmed factual information provided. NEVER invent fake phone numbers, fake email addresses, fake physical street addresses, or fake prices."*
2. **Prompt-Injection Defense**: Business descriptions and source contents contain untrusted user text. We wrap all external inputs in `<untrusted_source_content>` demarcation tags and prepend system instructions that command the model to treat content strictly as passive reference data, preventing prompt injection.
3. **Structured Schema Enforcement (`responseMimeType: 'application/json'`)**: Enables direct, reliable deserialization into PHP arrays without fragile regex scraping.
4. **Resilient Offline/Error Fallback**: If the Gemini API key is missing, invalid, rate-limited, or network unreachable, the application automatically invokes `buildFallbackWebsiteData()`. This deterministic engine constructs complete, styled, fact-grounded websites using confirmed facts, ensuring zero downtime.

---

## 4. Multi-Format Document Ingestion Strategy

### Decision:
Implement targeted, native server-side parsers for each document type rather than relying on external black-box cloud document parsing APIs.

### Rationale:
1. **Format-Specific Handlers**:
   - **PDF**: Parsed via `smalot/pdfparser`, extracting raw textual content from embedded streams.
   - **DOCX**: Unzipped via PHP's native `ZipArchive` to read `word/document.xml`, stripping XML tags to recover raw body text.
   - **TXT**: Read with multi-encoding fallback detection (`UTF-8`, `ASCII`, `ISO-8859-1`, `Windows-1252`).
   - **Images (JPG, PNG, WebP)**: Validated and stored for gallery usage and hero backgrounds.
2. **Handling Scanned (Non-OCR) PDFs**:
   - Scanned physical documents lack a text stream. Rather than silently failing or hallucinating content, the system detects empty parsed text on PDF uploads, marks the file as `scanned_ocr_required`, and informs the user to enter facts manually.

---

## 5. Web Research & SSRF Defense Architecture

### Decision:
Implement an outbound HTTP crawler protected by a multi-stage Server-Side Request Forgery (SSRF) validation pipeline.

### Rationale:
Allowing users to input arbitrary URLs for automated scraping introduces severe security risks: internal port scanning, access to AWS instance metadata (`169.254.169.254`), and loopback exploitation.

### Protection Pipeline:
1. **Protocol Whitelisting**: Only `http` and `https` schemes are accepted.
2. **Hostname Blacklisting**: Rejects `localhost`, `127.0.0.1`, `::1`, `0.0.0.0`, and `.local`/`.internal` domains.
3. **DNS Pre-Resolution & IP Range Checking**: Uses `gethostbyname()` to resolve hostnames before connection, rejecting any IP matching private network allocations (RFC 1918: `10.0.0.0/8`, `172.16.0.0/12`, `192.168.0.0/16`) or cloud link-local metadata addresses (`169.254.169.254`).
4. **Social Media Barrier Handling**: Headless requests to Instagram, Facebook, and LinkedIn frequently trigger bot challenges or HTTP 403s. The service detects these barriers and prompts the business owner for manual entry rather than crashing.

---

## 6. Fact Verification & Conflict Resolution Engine

### Decision:
Extract atomic facts with verbatim supporting evidence, categorize them into status states (`extracted`, `user-confirmed`, `conflicting`, `outdated`, `rejected`), and enforce owner-in-the-loop conflict resolution.

### Rationale:
1. **Why Atomic Extraction?** Extracting discrete key-value pairs (e.g. `phone`, `operating_hours`, `address`) allows comparing claims across independent sources.
2. **Conflict Detection**: If a brochure lists phone `+91 94220 54321` while a web page lists `+91 98220 99999`, both candidates are flagged as `conflicting`.
3. **Owner Decision Authority**: The application never guesses which conflicting fact is correct. It surfaces candidate claims alongside their source citations in the **Fact Review Dashboard**, requiring the owner to pick the accurate value or supply a manual correction.
4. **Pre-Flight Publishing Blocker**: Websites cannot be generated or published while unresolved conflicts exist, upholding the integrity of the published output.

---

## 7. Version History & Visual Section Customization

### Decision:
Store website layouts as modular section records in database tables (`website_sections`), snapshotting the entire website configuration into immutable `website_versions` upon significant edits and publishing events.

### Rationale:
1. **Section-Level Regeneration**: In the visual editor, owners can regenerate an individual section (e.g. `about` or `services`) with a custom prompt without altering the rest of the website.
2. **Side-by-Side Review & Approval**: Proposed regenerations are shown in a modal comparing current content vs. AI-proposed content. The database is updated only upon explicit user acceptance.
3. **Immutable Snapshots**: Every save and publish action creates a deep snapshot of all sections, colors, fonts, and settings. Owners can inspect previous versions and restore any historical state with a single click.

---

## 8. Public Publishing & Vanity URL Routing

### Decision:
Serve published websites under `/sites/{slug}` using direct database lookups with strict publication checks, vanity URL validation, and reserved slug blacklisting.

### Rationale:
1. **Privacy Protection**: An unpublished draft website must never leak business details to anonymous visitors. The controller executes:
   ```php
   if (!$website || !$website->is_published) {
       abort(404);
   }
   ```
2. **Collision Avoidance**: `WebsitePublishingService@generateUniqueSlug()` automatically handles slug collisions by appending incrementing numeric suffixes (`business-name-2`, `business-name-3`).
3. **Reserved Slugs**: Protects application routing integrity by preventing users from claiming system routes as vanity slugs (`admin`, `api`, `login`, `register`, `logout`, `dashboard`, `websites`, `sites`, `settings`, `profile`, `build`, `storage`).

---

## 9. Known Trade-Offs & Technical Debt

| Trade-Off | Context / Limitation | Mitigation in Current Architecture |
|---|---|---|
| **No OCR for Scanned PDFs** | Optical Character Recognition (Tesseract) requires complex native C-binary host dependencies that may not exist in all deployment environments. | The parser detects scanned documents and prompts the owner for manual data entry. |
| **Synchronous Document Processing** | Large multi-page documents (100+ pages) could exceed HTTP request execution limits if processed synchronously. | Files are limited to 15MB, and typical business brochures/menus process in < 2 seconds. |
| **Social Media Scraping Limits** | Public profiles on Meta/LinkedIn platforms actively restrict headless crawling. | Explicit login-wall detection alerts the owner immediately. |

---

## 10. Security Audit & Defensive Hardening (Phase 10)

### Decision:
Implement multi-layer defensive hardening across authentication rate limiting, SSRF multi-hop redirect filtering, CSS injection mitigation, and complete resource authorization coverage.

### Rationale:
1. **Brute-Force & Credential Stuffing Prevention**: Authentication endpoints (`POST /login`) throttle failed login attempts using Laravel's `RateLimiter` (5 attempts per minute per email + IP), returning HTTP 429 Too Many Requests and automatically clearing limits upon valid authentication.
2. **SSRF Redirect Hardening**: Standard HTTP clients follow 301/302 redirects automatically, which could allow an attacker to bypass initial URL inspection by redirecting from an external server to internal loopback (`http://127.0.0.1`, `[::1]`) or AWS/GCP metadata (`http://169.254.169.254`). We attached an `on_redirect` Guzzle hook that independently validates every redirect hop against private and reserved IP filters.
3. **CSS Context Breakout & Stored XSS**: Unescaped `{!! !!}` in CSS `<style>` blocks was hardened by enforcing strict regex validation (`/^[a-zA-Z0-9\s,\'\"\-]+$/`) on font families in `WebsiteEditorController@updateDesign` and regex sanitization in public Blade templates.
4. **100% Policy Authorization Coverage**: Added missing `update` authorization method to `UploadedFilePolicy` ensuring non-owners cannot modify or attach uploaded files belonging to another business.

---

## 11. Summary

Every architectural decision in **AI Builds My Website** was guided by two overarching criteria:
1. **Authenticity & Integrity**: The system must never hallucinate, invent claims, or publish unverified contradictions.
2. **Reliability & Production-Readiness**: The codebase must be robust, secure against SSRF and injection attacks, fully covered by automated tests, and capable of operating without crashes even when external APIs fail.
