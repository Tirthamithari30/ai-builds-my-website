# AI BUILDS MY WEBSITE
### Autonomous AI-Powered Business Website Generator

[![Laravel](https://img.shields.io/badge/Laravel-11.x-FF2D20?style=for-the-badge&logo=laravel&logoColor=white)](https://laravel.com)
[![PHP](https://img.shields.io/badge/PHP-8.4-777BB4?style=for-the-badge&logo=php&logoColor=white)](https://php.net)
[![MySQL](https://img.shields.io/badge/MySQL-8.0-4479A1?style=for-the-badge&logo=mysql&logoColor=white)](https://mysql.com)
[![Tailwind CSS](https://img.shields.io/badge/Tailwind_CSS-4.x-38B2AC?style=for-the-badge&logo=tailwind-css&logoColor=white)](https://tailwindcss.com)
[![Google Gemini](https://img.shields.io/badge/Google_Gemini-2.5_Flash-4285F4?style=for-the-badge&logo=google&logoColor=white)](https://ai.google.dev/)
[![Tests Passing](https://img.shields.io/badge/Tests-115%20Passed%20(100%25)-success?style=for-the-badge)](tests)

> **Developed for Websites.co.in Technical Internship Assignment**  
> An enterprise-grade, end-to-end full-stack SaaS web application that autonomously converts raw business information, uploaded documents (PDF, DOCX, TXT), and public online sources into verified, customizable, and publishable business websites using **Google Gemini AI**.

---

## Table of Contents

- [Core Value Proposition & Golden Rules](#core-value-proposition--golden-rules)
- [System Architecture](#system-architecture)
- [Key Features by Phase](#key-features-by-phase)
- [Technology Stack](#technology-stack)
- [Prerequisites & Requirements](#prerequisites--requirements)
- [Installation & Local Setup](#installation--local-setup)
- [Environment Configuration](#environment-configuration)
- [Database Setup & Migrations](#database-setup--migrations)
- [Running the Application](#running-the-application)
- [Automated Test Suite](#automated-test-suite)
- [Security & Defensive Hardening](#security--defensive-hardening)
- [Known Limitations](#known-limitations)
- [Repository Structure](#repository-structure)
- [License](#license)

---

## Core Value Proposition & Golden Rules

Unlike standard website builders or generic AI wrappers that hallucinate fake phone numbers, non-existent addresses, and fabricated reviews, **AI Builds My Website** is engineered around strict **Fact-Grounding and Auditability**:

1. **Golden Rule #1: Zero Hallucinated Business Claims**  
   Every factual claim (addresses, phone numbers, prices, services, operating hours) must be explicitly sourced from an uploaded document, a crawled source, or verified by the business owner.
2. **Golden Rule #2: Explicit Conflict Resolution**  
   When contradictory information is detected across different sources (e.g., conflicting phone numbers between an old brochure and a web listing), the system flags the conflict and blocks generation/publishing until the owner resolves it.
3. **Golden Rule #3: Real Backend Persistence**  
   No simulated state, no dummy responses, and no mock publishing. Every action persists to MySQL with full relational integrity and ownership authorization.
4. **Golden Rule #4: Draft Privacy Protection**  
   Unpublished draft websites are strictly isolated. Any anonymous visitor requesting an unpublished site or unknown vanity slug receives an authentic HTTP `404 Not Found`.

---

## System Architecture

```mermaid
flowchart TD
    subgraph Ingestion["Phase 3: Source Collection & Ingestion"]
        A1[Business Onboarding Details]
        A2[Document Uploads: PDF, DOCX, TXT, Images]
        A3[Public Web & Social URLs]
        A2 -->|Smalot PDFParser / ZipArchive| B1[Extracted Raw Text]
        A3 -->|SSRF-Protected HTTP Crawler| B1
        A1 --> B1
    end

    subgraph Verification["Phase 4: Fact Extraction & Conflict Engine"]
        B1 --> C1[Atomic Fact Extraction]
        C1 --> C2{Conflict Detection Engine}
        C2 -->|Competing Values| D1[Flagged as Conflicting - Review Required]
        C2 -->|Single Candidate| D2[Unverified / Source-Supported]
        D1 -->|Owner Action| E1[Confirmed Fact Dictionary]
        D2 -->|Owner Confirmation| E1
    end

    subgraph Generation["Phase 5: Gemini AI Generation Engine"]
        E1 --> F1[Grounded Prompt Payload]
        F1 --> F2[Google Gemini 2.5 Flash API]
        F2 -->|Structured JSON Output| G1[Website Models & 11 Section Types]
        F2 -.->|Offline / Failure Fallback| G2[Deterministic Grounded Builder]
        G2 --> G1
    end

    subgraph Customization["Phase 6: Visual Editor & Regeneration"]
        G1 --> H1[Split Workspace: Live Preview + Controls]
        H1 --> H2[Inline Content Editing]
        H1 --> H3[Gemini Section Regeneration with Side-by-Side Diff]
        H1 --> H4[Immutable Version Snapshots]
    end

    subgraph Publishing["Phase 7: Public Website Publishing"]
        H1 --> I1{Pre-Flight Quality Checklist}
        I1 -->|Valid| I2[Publish Live]
        I2 --> I3[Public Route: /sites/{slug}]
        I3 --> I4[Dynamic SEO, Open Graph & Canonical Tags]
        I2 -->|Unpublish| I5[Immediate 404 to Anonymous Visitors]
    end
```

---

## Key Features by Phase

### Phase 1: Environment & Foundation
- Modern Laravel 11 scaffolding on PHP 8.4 CLI and MySQL 8.0.
- Unified service architecture in `app/Services/` (`AiService`, `DocumentProcessingService`, `WebResearchService`, `SourceExtractionService`, `FactVerificationService`, `WebsiteGenerationService`, `WebsitePublishingService`).

### Phase 2: Authentication & Business Management
- Complete authentication: registration, login, logout with Bcrypt hashing, CSRF protection, and session security.
- Comprehensive Business CRUD with strict ownership enforcement (`BusinessPolicy`).
- Real database metric dashboard showing active projects, published sites, drafts, and extracted facts.

### Phase 3: Document Uploads & Source Collection
- Secure multi-format document parser supporting **PDF** (via `smalot/pdfparser`), **DOCX** (via `ZipArchive`), **TXT** (multi-encoding detection), and **Images** (`JPG`, `JPEG`, `PNG`, `WebP`).
- Robust web crawler with strict SSRF protection (loopback, private RFC 1918, and cloud metadata `169.254.169.254` blocking).
- Social media authentication wall detection for Instagram, Facebook, and LinkedIn.

### Phase 4: Fact Extraction & Conflict Verification
- Atomic fact extraction with verbatim supporting evidence citations.
- Automated contradiction detection: competing candidate values are flagged as `conflicting`.
- Verification actions: Confirm, Reject, Edit & Confirm, Mark Outdated, and Add Manual Fact.

### Phase 5: Gemini AI Website Generation Engine
- Pre-generation audit blocking websites with unresolved factual contradictions.
- Generation using Google Gemini 2.5 Flash with prompt-injection defenses and low temperature (0.2).
- Zero-hallucination deterministic fallback engine if AI is unconfigured or unreachable.
- Responsive preview canvas supporting Desktop, Tablet (768px), and Mobile (375px) viewports.

### Phase 6: Visual Website Editor & Customization
- Split-screen workspace with live iframe preview and reactive section editing.
- Section management: Add, Remove, Hide/Show, and Move Up / Down reordering.
- Gemini AI Section Regeneration with side-by-side original vs. proposed diff modal and owner approval workflow.
- Complete design styling: design presets, custom color pickers, typography fonts, button shapes, and section spacing.

### Phase 7: Public Website Publishing
- Live public website routing at `/sites/{slug}` for anonymous visitors.
- Strict draft privacy returning an HTTP 404 for unpublished websites.
- Pre-flight quality checklist verifying business name, visible sections, non-empty content, and absence of unresolved conflicts.
- Vanity URL management with reserved slug blacklisting (`admin`, `login`, `dashboard`, `sites`, etc.) and collision avoidance.
- Comprehensive SEO metadata: `<title>`, `<meta name="description">`, `<link rel="canonical">`, `<meta name="robots">`, Open Graph, and Twitter Cards.

---

## Technology Stack

| Layer | Technology | Purpose |
|---|---|---|
| **Runtime** | PHP 8.4+ CLI | High-performance backend engine |
| **Framework** | Laravel 11.x | MVC architecture, routing, policies, Eloquent ORM |
| **Database** | MySQL 8.0 | Relational data persistence with strict foreign key constraints |
| **Styling** | Tailwind CSS 4.x | Utility-first responsive design & custom theme variables |
| **Client Reactivity** | Alpine.js 3.x | Lightweight, reactive UI modals and live preview controls |
| **Templating** | Laravel Blade | Secure, XSS-escaped server-side rendering |
| **AI Engine** | Google Gemini 2.5 Flash | Grounded copywriting and section regeneration |
| **Document Parsing**| Smalot PDFParser & ZipArchive | Extraction from PDFs and Word documents |
| **Build Tool** | Vite 8.x | Modern frontend asset bundling |
| **Testing** | PHPUnit 11.x | Comprehensive automated feature and integration testing |

---

## Prerequisites & Requirements

Before setting up the project, ensure your environment meets the following specifications:

- **PHP**: `^8.2` or `^8.4` with extensions: `pdo_mysql`, `mbstring`, `openssl`, `curl`, `fileinfo`, `zip`, `gd`.
- **Composer**: `v2.x`
- **MySQL**: `8.0+` or MariaDB `10.4+`
- **Node.js**: `v18.x` or `v22.x` & **npm**: `v9.x+`
- **Git**

---

## Installation & Local Setup

### 1. Clone the Repository
```bash
git clone https://github.com/your-username/ai-builds-my-website.git
cd ai-builds-my-website
```

### 2. Install PHP Dependencies
```bash
composer install --no-interaction --prefer-dist --optimize-autoloader
```

### 3. Install Frontend Dependencies
```bash
npm install
```

---

## Environment Configuration

Copy the example environment configuration:
```bash
cp .env.example .env
```

Generate the unique application encryption key:
```bash
php artisan key:generate
```

Configure your local MySQL database and Gemini credentials in `.env`:
```ini
APP_NAME="AI Builds My Website"
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost:8000

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=ai_builds_my_website
DB_USERNAME=root
DB_PASSWORD=your_mysql_password

# Google Gemini AI Configuration
GEMINI_API_KEY=your_actual_gemini_api_key_here
GEMINI_MODEL=gemini-2.5-flash

# Optional: Serper Web Search API Key
SEARCH_PROVIDER=serper
SEARCH_API_KEY=your_serper_api_key_here
```

Create the local filesystem symlink for document and asset storage:
```bash
php artisan storage:link
```

---

## Database Setup & Migrations

Ensure your MySQL server is running and create the database:
```sql
CREATE DATABASE IF NOT EXISTS ai_builds_my_website CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

Run the database migrations:
```bash
php artisan migrate
```

---

## Running the Application

### 1. Compile Frontend Assets
For local development with hot module reloading:
```bash
npm run dev
```
Or build minified assets for production:
```bash
npm run build
```

### 2. Start the Local Server
```bash
php artisan serve
```

Visit the application in your browser at:  
👉 **`http://localhost:8000`**

---

## Automated Test Suite

The application includes an extensive, end-to-end automated test suite covering all 10 completed phases:
- Authentication, Rate Limiting & Session Management
- Business Ownership & Granular Policy Isolation
- Document Upload Validation & Multi-Format Parsing
- Outbound Web Crawler with SSRF & Multi-Hop Redirect Defense
- Fact Extraction, Evidence Grounding & Conflict Resolution
- Gemini 2.5 Flash Website Generation Engine with Resilient Offline Fallback
- Visual Editor, AI Section Regeneration & Immutable Version Snapshots
- Public Website Publishing, Unique Vanity Slugs, SEO Tags & 404 Draft Isolation
- Multi-Business Real-World End-to-End Verification
- Complete Application Security Audit & Defensive Hardening

### Run All Tests
```bash
php artisan test
```

### Actual Test Suite Execution Results:
```text
   PASS  Tests\Feature\PhaseOneEnvironmentTest (5 tests, 21 assertions)
   PASS  Tests\Feature\PhaseTwoAuthAndBusinessTest (10 tests, 48 assertions)
   PASS  Tests\Feature\PhaseThreeDocumentAndSourceTest (12 tests, 56 assertions)
   PASS  Tests\Feature\PhaseFourFactVerificationTest (14 tests, 68 assertions)
   PASS  Tests\Feature\PhaseFiveWebsiteGenerationTest (16 tests, 78 assertions)
   PASS  Tests\Feature\PhaseSixWebsiteEditorTest (16 tests, 78 assertions)
   PASS  Tests\Feature\PhaseSevenWebsitePublishingTest (14 tests, 67 assertions)
   PASS  Tests\Feature\PhaseEightEndToEndIntegrationTest (2 tests, 69 assertions)
   PASS  Tests\Feature\PhaseNineSystemTestingAndBugFixingTest (13 tests, 67 assertions)
   PASS  Tests\Feature\PhaseTenSecurityAuditTest (13 tests, 71 assertions)

Tests:    115 passed (115 total)
Assertions: 623
Duration: ~10.6s
Result:   100% PASSING
```

---

## Security & Defensive Hardening

- **Authentication Rate Limiting**: Built-in IP and email throttling (5 attempts/minute) on login routes to prevent brute-force credential stuffing, with automatic throttle clearance upon successful authentication.
- **SSRF Defense with Redirect Protection**: Outbound crawler resolves DNS before initiating requests and strictly blocks private IP ranges (`10.0.0.0/8`, `172.16.0.0/12`, `192.168.0.0/16`), loopback (`127.0.0.1`, `::1`), and cloud metadata (`169.254.169.254`). Includes Guzzle `on_redirect` validation to block multi-hop HTTP 301/302 redirects targeting internal or metadata services.
- **File Upload Protection**: Enforces server-side MIME type inspection, a 15MB file ceiling, blocks executable extensions, and stores files with random UUIDs in isolated per-business storage.
- **Cross-User Data Isolation (IDOR Prevention)**: Every controller interaction is guarded with Laravel Policies (`BusinessPolicy`, `WebsitePolicy`, `UploadedFilePolicy`, `BusinessSourcePolicy`), returning HTTP 403 Forbidden on any unauthorized cross-tenant attempt.
- **XSS & CSS Breakout Prevention**: All Blade user output is strictly escaped with `{{ }}`. Dynamic CSS custom properties (`--site-font`) enforce strict alphanumeric/safe punctuation regex validation and Blade sanitization, eliminating CSS injection and breakout vectors.
- **SQL Injection Prevention**: All database queries utilize Eloquent ORM and PDO prepared statements with strict parameter binding (zero unparameterized SQL).
- **Prompt Injection Defense**: Untrusted text is sequestered inside `<untrusted_source_content>` wrappers with system instructions commanding the model never to interpret user data as executable instructions.

---

## Known Limitations

1. **Scanned Images / Non-OCR PDFs**: Documents consisting solely of scanned physical images without an embedded text layer cannot be parsed via `smalot/pdfparser`. The system flags these documents as `scanned_ocr_required` and prompts the user to enter facts manually.
2. **Social Media Login Barriers**: Instagram, Facebook, and LinkedIn frequently require user authentication and block headless server requests. The crawler detects these login walls gracefully and prompts the owner for manual details.
3. **Web Search API Dependency**: Online business searching requires an active `SEARCH_API_KEY` (e.g. Serper). If omitted, the system informs the user and allows manual URL entry without crashing.

---

## Repository Structure

```text
ai-builds-my-website/
├── app/
│   ├── Http/Controllers/        # Controllers (Dashboard, Business, Sources, Facts, Editor, Publishing)
│   ├── Http/Requests/           # Form request validators (StoreBusinessRequest, etc.)
│   ├── Models/                  # Eloquent models (Business, Website, Section, Fact, Publication)
│   ├── Policies/                # Authorization policies (BusinessPolicy, WebsitePolicy, etc.)
│   └── Services/                # Core service layer
│       ├── AiService.php                  # Google Gemini API integration & injection defense
│       ├── DocumentProcessingService.php  # Multi-format parser (PDF, DOCX, TXT)
│       ├── FactVerificationService.php    # Conflict detection & fact dictionary management
│       ├── SourceExtractionService.php    # Atomic regex & heuristic extraction
│       ├── WebResearchService.php         # SSRF-protected crawler & search integration
│       ├── WebsiteGenerationService.php   # Layout assembly & Gemini copywriting
│       └── WebsitePublishingService.php   # Pre-flight audit, vanity slugs, & publishing
├── database/
│   └── migrations/              # Database schema migrations
├── resources/
│   ├── views/
│   │   ├── businesses/          # Business creation, sources, and fact review views
│   │   ├── dashboard/           # Metrics dashboard & project management
│   │   ├── layouts/             # Base layouts with navigation & flash alerts
│   │   ├── templates/           # Public website Blade templates & 11 section partials
│   │   └── websites/            # Setup, visual editor, and responsive preview canvas
├── routes/
│   └── web.php                  # Public and authenticated route definitions
├── tests/
│   └── Feature/                 # 89 comprehensive feature and integration tests
├── .env.example                 # Sanitized environment template
├── DECISIONS.md                 # Architectural Decision Records (ADRs)
└── README.md                    # Project documentation
```

---

## License

This project is developed as a technical evaluation assignment for **Websites.co.in**.  
Released under the [MIT License](LICENSE).
