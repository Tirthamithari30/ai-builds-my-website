# USER GUIDE: AI BUILDS MY WEBSITE

Welcome to **AI Builds My Website**! This comprehensive user manual guides you through every step of transforming your business documents and information into a professional, mobile-ready commercial website.

---

## Table of Contents

1. [Account Registration & Login](#1-account-registration--login)
2. [Managing Your Dashboard](#2-managing-your-dashboard)
3. [Creating a Business Profile](#3-creating-a-business-profile)
4. [Uploading Documents & Adding Web Links](#4-uploading-documents--adding-web-links)
5. [Verifying Facts & Resolving Conflicts](#5-verifying-facts--resolving-conflicts)
6. [Generating Your Website with AI](#6-generating-your-website-with-ai)
7. [Live Responsive Preview](#7-live-responsive-preview)
8. [Editing Website Sections & Inline Content](#8-editing-website-sections--inline-content)
9. [Regenerating Specific Sections with AI](#9-regenerating-specific-sections-with-ai)
10. [Publishing Your Website](#10-publishing-your-website)
11. [Accessing the Public URL](#11-accessing-the-public-url)
12. [Updating and Unpublishing Your Website](#12-updating-and-unpublishing-your-website)

---

## 1. Account Registration & Login

### How to Register a New Account
1. Open your browser and navigate to the application homepage: [http://127.0.0.1:8000](http://127.0.0.1:8000).
2. Click the **Get Started** button in the top navigation bar.
3. On the registration screen, enter:
   - **Full Name** (e.g., `Rajesh Patil`)
   - **Email Address** (e.g., `rajesh@example.com`)
   - **Password** (minimum 8 characters)
   - **Confirm Password**
4. Click **Create Account**. You will be logged in immediately and redirected to your Dashboard.

### How to Log In
1. Navigate to [http://127.0.0.1:8000/login](http://127.0.0.1:8000/login).
2. Enter your registered email address and password.
3. Click **Sign In**.

> **Pre-Seeded Demonstration Account:**  
> Email: `demo@websites.co.in`  
> Password: `password123`

---

## 2. Managing Your Dashboard

The Dashboard ([http://127.0.0.1:8000/dashboard](http://127.0.0.1:8000/dashboard)) is your central operational hub:
- **Key Metrics:** View your total businesses, published websites, draft websites, and confirmed facts at a glance.
- **Projects Grid:** Lists all businesses you own. Each business card shows its category, location, publication status, and quick-action buttons:
  - **Manage Sources & Facts**: Upload files or verify details.
  - **Generate / View Website**: Open the website generator or visual editor.
  - **Public Link**: If published, view the live site in a new tab.

---

## 3. Creating a Business Profile

1. From your Dashboard, click **+ Add New Business**.
2. Complete the profile fields:
   - **Business Name** (e.g., `Patil Traditional Spices & Foods`)
   - **Category**: Select from *Retail Store*, *Restaurant*, *Salon & Spa*, *Consulting*, *Healthcare / Professional Services*, etc.
   - **Phone Number** (e.g., `+91 94220 54321`)
   - **Email Address** (e.g., `orders@patilspices.com`)
   - **City & State** (e.g., `Kolhapur, Maharashtra`)
   - **Brief Description**: A short summary of what your business does.
3. Click **Save & Continue**.

---

## 4. Uploading Documents & Adding Web Links

The platform extracts facts from your real-world business materials:
1. Navigate to your business workspace and click **Upload Documents & Sources**.
2. **File Uploads**:
   - Drag and drop or browse for files (PDF, TXT, CSV, DOCX, PNG, JPG).
   - Maximum file size: 15MB.
   - Examples: Price cards, product lists, brochures, clinic schedules.
3. **Web Link Scraping**:
   - Enter an existing website or social page URL (e.g., `https://example.com/about`).
   - Click **Ingest & Crawl**. The system securely parses text while blocking unsafe or private networks.
4. Click **Process & Extract Facts**.

---

## 5. Verifying Facts & Resolving Conflicts

The **Fact Grounding Engine** prevents AI hallucinations by requiring verified evidence:
1. Review the list of detected facts:
   - Contact numbers, email addresses, street address, operating hours, product/service items.
2. **Confirm Facts**: Click the green **Confirm** checkmark next to each accurate fact.
3. **Resolve Conflicts**: If an old brochure listed an outdated phone number while your new card has a new number, the system displays a yellow conflict warning. Simply click the accurate value to resolve the discrepancy.
4. **Add Manual Facts**: Need to add an extra service? Use the **+ Add Manual Fact** form.

---

## 6. Generating Your Website with AI

Once your facts are confirmed:
1. Click **Generate Website**.
2. **Select Design Preset**:
   - **Modern Minimal**: Clean, high-tech, balanced typography.
   - **Warm Artisan**: Earthy tones, handcrafted typography (ideal for food, bakeries, crafts).
   - **Bold Creative**: Dynamic gradients, high contrast (ideal for fitness, salons, agencies).
   - **Corporate Clean**: Blue & slate tones, structured authority (ideal for clinics, consulting, legal).
   - **Premium Elegant**: Serif headings, gold accents, luxurious feel.
3. **Customize Branding**: Select your primary and secondary brand colors.
4. Click **Generate Website with AI**. In seconds, Gemini 2.5 Flash constructs your complete multi-section website.

---

## 7. Live Responsive Preview

The **Website Editor** ([http://127.0.0.1:8000/websites/{id}/edit](http://127.0.0.1:8000/websites/{id}/edit)) features an embedded live interactive preview:
- **Device Viewport Toggles**:
  - **Desktop** (100% full width)
  - **Tablet** (768px frame)
  - **Mobile** (375px phone frame)
- Test navigation links, responsive layouts, hero typography, and accordion FAQs in real-time.

---

## 8. Editing Website Sections & Inline Content

You have complete control over every word on your website:
1. In the editor sidebar, view your list of sections: *Hero*, *About*, *Features*, *Services / Products*, *Testimonials*, *FAQ*, *Contact*, *Footer*.
2. **Reorder Sections**: Use the up and down arrows to arrange sections in your preferred order.
3. **Toggle Visibility**: Switch the eye toggle to hide or show any section.
4. **Edit Content**: Click any section to open its editable fields:
   - Headings, subheadings, badges.
   - Button text and button links.
   - Product/service names, descriptions, and pricing.
5. Click **Save Changes**. The preview refreshes instantly.

---

## 9. Regenerating Specific Sections with AI

Want a fresher headline or more compelling *About* copy?
1. Open the specific section in the editor.
2. Click **Regenerate with AI**.
3. (Optional) Provide a directive (e.g., *"Make the tone more warm and community-focused"*).
4. Review the AI-generated proposal side-by-side with your current text.
5. Click **Apply Changes** to update the section or **Discard** to keep your current version.

---

## 10. Publishing Your Website

When you are satisfied with your website:
1. Click the green **Publish Website** button at the top right of the editor.
2. The platform generates an immutable version snapshot and assigns your vanity URL slug (e.g., `patil-traditional-spices`).
3. You will receive a success notification with your live public link.

---

## 11. Accessing the Public URL

Your published website is live and globally accessible without requiring login:
- **Public URL Format**: `http://127.0.0.1:8000/sites/{your-slug}`
- **Test in Incognito Mode**: Open a private/incognito browser window and paste the URL.
- **Inspect SEO**: View page source to verify automatic `<title>`, `<meta description>`, OpenGraph, and Twitter card tags.

---

## 12. Updating and Unpublishing Your Website

### Making Updates to a Live Site
1. Open the website in the visual editor.
2. Modify text, pricing, or styling as needed.
3. Click **Publish Updates**. Your live URL updates immediately without downtime.

### How to Unpublish
1. In the editor or on your dashboard, click **Unpublish**.
2. Confirm the prompt.
3. The website immediately reverts to draft status. Any visitor attempting to load the public URL will receive a clean `404 Not Found` page, ensuring no unfinished work is visible to the public.
4. You can re-publish your website at any time with a single click.
