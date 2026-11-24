# API & Route Reference (API.md)
## AI BUILDS MY WEBSITE — HTTP Route & Controller Specification

This document provides a comprehensive reference of all HTTP routes, methods, authentication requirements, and controllers implemented in **AI Builds My Website**.

---

## 1. Route Summary by Module

| Module | Base Path | Middleware | Description |
|---|---|---|---|
| **Public & Marketing** | `/`, `/up`, `/sites/{slug}` | None / Web | Public marketing landing page, system health probe, and live published business websites. |
| **Authentication** | `/login`, `/register`, `/logout` | `guest` / `auth` | User account creation, authentication with rate limiting, and session destruction. |
| **Dashboard** | `/dashboard` | `auth` | SaaS metrics overview, project lists, quick links. |
| **Business Management** | `/businesses/*` | `auth` | Full CRUD operations for user-owned business profiles. |
| **Document & Sources** | `/businesses/{id}/sources`, `/files` | `auth` | Document file uploads, public web URLs, and social profile ingestion. |
| **Fact Verification** | `/businesses/{id}/facts/*` | `auth` | Atomic fact extraction, conflict resolution, manual fact entry. |
| **Website Generation** | `/businesses/{id}/website/*` | `auth` | Pre-generation fact audit, Gemini AI generation engine, version snapshots. |
| **Visual Website Editor** | `/websites/{id}/edit`, `/sections/*` | `auth` | Live preview canvas, section CRUD, reordering, AI section regeneration diffs. |
| **Website Publishing** | `/websites/{id}/publish`, `/seo` | `auth` | Pre-flight quality checklist, vanity slugs, public activation, SEO metadata. |

---

## 2. Complete Route Reference

### 2.1 Public & Visitor Endpoints

#### `GET /`
- **Route Name:** `home`
- **Controller Action:** Closure (`welcome.blade.php`)
- **Middleware:** `web`
- **Description:** Renders the public SaaS landing page, hero section, feature showcases, interactive workflow demo, and call-to-action buttons.

#### `GET /up`
- **Route Name:** None
- **Controller Action:** Laravel Health Check
- **Middleware:** None
- **Description:** Used by load balancers, Docker, and uptime monitoring services. Returns HTTP 200 OK when the framework boots successfully.

#### `GET /sites/{slug}`
- **Route Name:** `sites.show`
- **Controller Action:** `WebsitePublishingController@showPublic`
- **Middleware:** `web`, `SecurityHeaders`
- **Parameters:**
  - `slug` (string, required): The unique published vanity slug (e.g. `patil-spices`, `kulkarni-clinic`).
- **Responses:**
  - `200 OK`: Renders the live, responsive public website with full SEO, Open Graph, and Twitter metadata.
  - `404 Not Found`: Returned immediately if the slug does not exist OR if the website is in draft/unpublished status.

---

### 2.2 Authentication Endpoints

#### `GET /register`
- **Route Name:** `register`
- **Controller Action:** `RegisterController@showRegistrationForm`
- **Middleware:** `guest`
- **Description:** Displays the user registration form.

#### `POST /register`
- **Route Name:** `register`
- **Controller Action:** `RegisterController@register`
- **Middleware:** `guest`
- **Payload:**
  - `name` (string, required, max: 255)
  - `email` (string, required, email, unique: users)
  - `password` (string, required, min: 8, confirmed)
- **Response:** 302 Redirect to `/dashboard` upon account creation.

#### `GET /login`
- **Route Name:** `login`
- **Controller Action:** `LoginController@showLoginForm`
- **Middleware:** `guest`
- **Description:** Displays the user login form.

#### `POST /login`
- **Route Name:** `login`
- **Controller Action:** `LoginController@login`
- **Middleware:** `guest`, `RateLimiter` (5 attempts/min)
- **Payload:**
  - `email` (string, required, email)
  - `password` (string, required)
  - `remember` (boolean, optional)
- **Responses:**
  - `302 Redirect`: Redirects to intended page or `/dashboard` on successful authentication.
  - `429 Too Many Requests`: Triggered when IP/email exceeds 5 failed attempts in 60 seconds.

#### `POST /logout`
- **Route Name:** `logout`
- **Controller Action:** `LoginController@logout`
- **Middleware:** `auth`
- **Description:** Invalidates the current session, regenerates CSRF token, and redirects to `/`.

---

### 2.3 Dashboard & Business Management

#### `GET /dashboard`
- **Route Name:** `dashboard`
- **Controller Action:** `DashboardController@index`
- **Middleware:** `auth`
- **Description:** Returns aggregate project counts, draft websites, published websites, and recently updated businesses for the authenticated user.

#### `GET /businesses`
- **Route Name:** `businesses.index`
- **Controller Action:** `BusinessController@index`
- **Description:** Lists all businesses owned by the authenticated user with search and filtering.

#### `GET /businesses/create`
- **Route Name:** `businesses.create`
- **Controller Action:** `BusinessController@create`
- **Description:** Renders the business onboarding form.

#### `POST /businesses`
- **Route Name:** `businesses.store`
- **Controller Action:** `BusinessController@store`
- **Payload:**
  - `name` (string, required, max: 255)
  - `category` (string, nullable)
  - `city` (string, nullable)
  - `state` (string, nullable)
  - `country` (string, nullable)
  - `phone` (string, nullable)
  - `email` (string, nullable, email)
  - `address` (string, nullable)
  - `website_url` (string, nullable, url)
  - `description` (string, nullable)
  - `products` (string|array, nullable)
  - `services` (string|array, nullable)
  - `operating_hours` (string|array, nullable)

#### `GET /businesses/{business}`
- **Route Name:** `businesses.show`
- **Controller Action:** `BusinessController@show`
- **Authorization:** `BusinessPolicy@view`

#### `GET /businesses/{business}/edit`
- **Route Name:** `businesses.edit`
- **Controller Action:** `BusinessController@edit`
- **Authorization:** `BusinessPolicy@update`

#### `PUT /businesses/{business}`
- **Route Name:** `businesses.update`
- **Controller Action:** `BusinessController@update`
- **Authorization:** `BusinessPolicy@update`

#### `DELETE /businesses/{business}`
- **Route Name:** `businesses.destroy`
- **Controller Action:** `BusinessController@destroy`
- **Authorization:** `BusinessPolicy@delete`

---

### 2.4 Sources & Document Ingestion

#### `GET /businesses/{business}/sources`
- **Route Name:** `businesses.sources.index`
- **Controller Action:** `BusinessSourceController@index`
- **Description:** Lists all connected web URLs, social profiles, and uploaded documents for the business.

#### `POST /businesses/{business}/sources`
- **Route Name:** `businesses.sources.store`
- **Controller Action:** `BusinessSourceController@store`
- **Payload:**
  - `source_type` (in: `website`, `google_business`, `instagram`, `facebook`, `linkedin`, `youtube`, `other`)
  - `url` (required, valid URL, SSRF-filtered)

#### `PUT /sources/{source}`
- **Route Name:** `sources.update`
- **Controller Action:** `BusinessSourceController@update`

#### `DELETE /sources/{source}`
- **Route Name:** `sources.destroy`
- **Controller Action:** `BusinessSourceController@destroy`

#### `POST /sources/{source}/retry`
- **Route Name:** `sources.retry`
- **Controller Action:** `BusinessSourceController@retry`
- **Description:** Re-triggers crawler extraction on a failed or blocked source.

#### `POST /businesses/{business}/files`
- **Route Name:** `businesses.files.store`
- **Controller Action:** `UploadedFileController@store`
- **Payload:**
  - `file` (required, file, max: 15360 KB, mimes: `pdf,doc,docx,txt,png,jpg,jpeg,webp`)

#### `DELETE /files/{file}`
- **Route Name:** `files.destroy`
- **Controller Action:** `UploadedFileController@destroy`

#### `POST /files/{file}/retry`
- **Route Name:** `files.retry`
- **Controller Action:** `UploadedFileController@retry`

---

### 2.5 Fact Verification & Conflict Resolution

#### `GET /businesses/{business}/facts`
- **Route Name:** `businesses.facts.index`
- **Controller Action:** `FactVerificationController@index`
- **Description:** Surfaces all candidate business facts grouped by category with conflict indicators, evidence excerpts, and confirmation buttons.

#### `POST /facts/{fact}/confirm`
- **Route Name:** `facts.confirm`
- **Controller Action:** `FactVerificationController@confirm`
- **Description:** Confirms an unverified candidate fact into the verified fact dictionary.

#### `PUT /facts/{fact}/edit-confirm`
- **Route Name:** `facts.edit-confirm`
- **Controller Action:** `FactVerificationController@editAndConfirm`
- **Payload:**
  - `fact_value` (required, string)

#### `POST /facts/{fact}/reject`
- **Route Name:** `facts.reject`
- **Controller Action:** `FactVerificationController@reject`

#### `POST /facts/{fact}/outdated`
- **Route Name:** `facts.outdated`
- **Controller Action:** `FactVerificationController@markOutdated`

#### `POST /businesses/{business}/facts/resolve-conflict`
- **Route Name:** `businesses.facts.resolve-conflict`
- **Controller Action:** `FactVerificationController@resolveConflict`
- **Payload:**
  - `fact_category` (required, string)
  - `chosen_fact_id` (nullable, integer)
  - `custom_value` (nullable, string)

#### `POST /businesses/{business}/facts/manual`
- **Route Name:** `businesses.facts.manual`
- **Controller Action:** `FactVerificationController@addManual`
- **Payload:**
  - `fact_category` (required, string)
  - `fact_key` (required, string)
  - `fact_value` (required, string)

#### `POST /businesses/{business}/facts/reextract`
- **Route Name:** `businesses.facts.reextract`
- **Controller Action:** `FactVerificationController@reextract`

---

### 2.6 Website Generation & Snapshots

#### `GET /businesses/{business}/website/setup`
- **Route Name:** `businesses.website.setup`
- **Controller Action:** `WebsiteGenerationController@setup`
- **Description:** Pre-generation review audit displaying confirmed facts and blocking generation if unresolved conflicts exist.

#### `POST /businesses/{business}/website/generate`
- **Route Name:** `businesses.website.generate`
- **Controller Action:** `WebsiteGenerationController@generate`
- **Payload:**
  - `title` (required, string)
  - `design_preset` (in: `modern_minimal`, `bold_vibrant`, `warm_artisan`, `clinical_serene`, `corporate_clean`)
  - `primary_color` (hex color)
  - `secondary_color` (hex color)
  - `sections` (array of section types)

#### `GET /websites/{website}/preview`
- **Route Name:** `websites.preview`
- **Controller Action:** `WebsiteGenerationController@preview`
- **Description:** Renders the responsive device container (Desktop / Tablet / Mobile) wrapping the preview iframe.

#### `GET /websites/{website}/preview-frame`
- **Route Name:** `websites.preview.frame`
- **Controller Action:** `WebsiteGenerationController@previewFrame`
- **Description:** Renders the actual raw website HTML loaded inside the preview iframe.

#### `GET /websites/{website}/versions`
- **Route Name:** `websites.versions`
- **Controller Action:** `WebsiteGenerationController@versions`
- **Description:** Displays chronological immutable snapshot history.

#### `POST /websites/{website}/versions/{version}/restore`
- **Route Name:** `websites.versions.restore`
- **Controller Action:** `WebsiteGenerationController@restoreVersion`

---

### 2.7 Visual Website Editor & AI Regeneration

#### `GET /websites/{website}/edit`
- **Route Name:** `websites.edit`
- **Controller Action:** `WebsiteEditorController@edit`
- **Description:** Full-screen visual editor workspace with split controls and preview canvas.

#### `POST /websites/{website}/sections`
- **Route Name:** `websites.sections.add`
- **Controller Action:** `WebsiteEditorController@addSection`
- **Payload:**
  - `section_type` (required, in: 11 supported section types)
  - `title` (optional, string)

#### `PUT /sections/{section}`
- **Route Name:** `sections.update`
- **Controller Action:** `WebsiteEditorController@updateSection`
- **Payload:**
  - `title` (string)
  - `subtitle` (string, nullable)
  - `content` (array or JSON)

#### `DELETE /sections/{section}`
- **Route Name:** `sections.delete`
- **Controller Action:** `WebsiteEditorController@deleteSection`

#### `POST /sections/{section}/toggle-visibility`
- **Route Name:** `sections.toggle-visibility`
- **Controller Action:** `WebsiteEditorController@toggleVisibility`

#### `POST /websites/{website}/sections/reorder`
- **Route Name:** `websites.sections.reorder`
- **Controller Action:** `WebsiteEditorController@reorderSections`
- **Payload:**
  - `order` (array of section IDs in desired order)

#### `POST /sections/{section}/regenerate-preview`
- **Route Name:** `sections.regenerate-preview`
- **Controller Action:** `WebsiteEditorController@regenerateSectionPreview`
- **Payload:**
  - `user_prompt` (string, nullable)
- **Response:** JSON containing `{ success: true, original: {...}, proposed: {...} }` for modal diff review.

#### `POST /sections/{section}/apply-regeneration`
- **Route Name:** `sections.apply-regeneration`
- **Controller Action:** `WebsiteEditorController@applySectionRegeneration`
- **Payload:**
  - `proposed_content` (array)

#### `PUT /websites/{website}/design`
- **Route Name:** `websites.design.update`
- **Controller Action:** `WebsiteEditorController@updateDesign`
- **Payload:**
  - `primary_color` (hex)
  - `secondary_color` (hex)
  - `accent_color` (hex)
  - `font_family` (string, regex: `/^[a-zA-Z0-9\s,\'\"\-]+$/`)

#### `POST /websites/{website}/save-all`
- **Route Name:** `websites.save-all`
- **Controller Action:** `WebsiteEditorController@saveAll`
- **Payload:**
  - `sections` (array of section content updates)

---

### 2.8 Website Publishing & Public URLs

#### `POST /websites/{website}/publish`
- **Route Name:** `websites.publish`
- **Controller Action:** `WebsitePublishingController@publish`
- **Payload:**
  - `vanity_slug` (optional, slug string)
- **Behavior:** Validates pre-flight rules, assigns unique slug, creates publication record, and activates public URL.

#### `POST /websites/{website}/unpublish`
- **Route Name:** `websites.unpublish`
- **Controller Action:** `WebsitePublishingController@unpublish`
- **Behavior:** Deactivates public access; visits to `/sites/{slug}` instantly return HTTP 404.

#### `PUT /websites/{website}/slug`
- **Route Name:** `websites.slug.update`
- **Controller Action:** `WebsitePublishingController@updateSlug`
- **Payload:**
  - `slug` (required, slug, reserved-word checked)

#### `PUT /websites/{website}/seo`
- **Route Name:** `websites.seo.update`
- **Controller Action:** `WebsitePublishingController@updateSeo`
- **Payload:**
  - `seo_title` (string, nullable, max: 70)
  - `seo_description` (string, nullable, max: 160)
  - `og_image` (string, nullable, url)
