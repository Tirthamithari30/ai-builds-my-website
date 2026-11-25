# DEVELOPER INSTALLATION GUIDE: AI BUILDS MY WEBSITE

> **Target Audience:** Developers, DevOps Engineers, and Technical Evaluators  
> **Platform Support:** Windows, macOS, Linux (Ubuntu 22.04/24.04 LTS)  
> **Repository:** `ai-builds-my-website`  

This guide provides step-by-step instructions for installing and running the **AI Builds My Website** platform on a clean machine.

---

## 1. System Requirements & Prerequisites

Ensure the following runtimes and services are installed on your host system:

| Component | Minimum Version | Recommended Version | Verification Command |
|---|---|---|---|
| **PHP** | 8.2+ | **8.4.x** | `php -v` |
| **Composer** | 2.5+ | **2.7.x+** | `composer --version` |
| **Node.js** | 18.x+ | **20.x+ (LTS)** | `node -v` |
| **npm** | 9.x+ | **10.x+** | `npm -v` |
| **MySQL Server** | 8.0+ | **8.0.44+** | `mysql --version` |

### Required PHP Extensions
Ensure these extensions are enabled in your `php.ini`:
- `pdo_mysql`
- `openssl`
- `mbstring`
- `tokenizer`
- `xml`
- `ctype`
- `json`
- `bcmath`
- `curl`
- `fileinfo` (mandatory for document MIME type verification)

---

## 2. Clone the Repository

Clone the project repository to your desired working directory:

```bash
git clone https://github.com/your-username/ai-builds-my-website.git
cd ai-builds-my-website
```

---

## 3. Install Backend Dependencies

Run Composer to install all required PHP packages:

```bash
composer install --no-interaction --prefer-dist --optimize-autoloader
```

---

## 4. Install Frontend Dependencies

Install all JavaScript and CSS dependencies:

```bash
npm install
```

---

## 5. Configure Environment Variables

Create your local `.env` configuration file from the provided template:

```bash
# On Linux / macOS:
cp .env.example .env

# On Windows PowerShell:
Copy-Item .env.example .env
```

### Generate Application Encryption Key
Generate a secure 32-character base64 key:

```bash
php artisan key:generate
```

---

## 6. Setup the Database

### A. Create MySQL Database
Log into MySQL and create the database:

```sql
CREATE DATABASE ai_builds_my_website CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

### B. Configure Database Credentials in `.env`
Open your `.env` file and set your MySQL connection parameters:

```dotenv
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=ai_builds_my_website
DB_USERNAME=root
DB_PASSWORD=your_mysql_password
```

### C. Run Database Migrations
Execute the migrations to build the complete 10-table schema:

```bash
php artisan migrate
```

---

## 7. Configure Gemini AI API Key (Optional but Recommended)

The platform integrates with **Google Gemini 2.5 Flash**:

1. Obtain an API key from [Google AI Studio](https://aistudio.google.com/).
2. Add your key to `.env`:

```dotenv
GEMINI_API_KEY=AIzaSyYourActualGeminiApiKeyHere
GEMINI_MODEL=gemini-2.5-flash
```

> **Note on Offline Resilience:**  
> If `GEMINI_API_KEY` is left blank, the application automatically activates its deterministic fact-grounded synthesis engine. The website generation and section editing features remain 100% functional without an API key!

---

## 8. Link Storage for File Uploads

Create the symbolic link from `public/storage` to `storage/app/public` so uploaded images and brochures can be displayed:

```bash
php artisan storage:link
```

---

## 9. Seed Authentic Demonstration Businesses

Populate the database with complete multi-business demonstration data:

```bash
php artisan demo:populate
```

This creates:
1. **Patil Traditional Spices & Foods** (Retail, Kolhapur) -> `/sites/patil-traditional-spices`
2. **Dr. Kulkarni Ayurvedic Wellness Clinic** (Healthcare, Pune) -> `/sites/dr-kulkarni-ayurveda`
3. **Universal Demo Reviewer Account**:
   - Email: `demo@websites.co.in`
   - Password: `password123`

---

## 10. Build Frontend Assets

Compile production-ready Tailwind CSS 4 and Alpine.js assets:

```bash
npm run build
```

*(During development, you may alternately run `npm run dev` for hot module replacement).*

---

## 11. Start the Application Web Server

Start Laravel's built-in web server:

```bash
php artisan serve --host=127.0.0.1 --port=8000
```

The application is now accessible at:
- **Application Portal:** [http://127.0.0.1:8000](http://127.0.0.1:8000)
- **Sign In:** [http://127.0.0.1:8000/login](http://127.0.0.1:8000/login)
- **Live Demo Site 1:** [http://127.0.0.1:8000/sites/patil-traditional-spices](http://127.0.0.1:8000/sites/patil-traditional-spices)
- **Live Demo Site 2:** [http://127.0.0.1:8000/sites/dr-kulkarni-ayurveda](http://127.0.0.1:8000/sites/dr-kulkarni-ayurveda)

---

## 12. Running Automated Tests

Run the complete 172-test suite to verify system integrity:

```bash
php artisan test
```

Expected result:
```text
Tests:    172 passed (172 total)
Assertions: 1,032 passed
Status:   100% Green
```

---

## 13. Troubleshooting Common Issues

### Issue: Port 8000 is already in use
**Solution:** Specify an alternative port:
```bash
php artisan serve --port=8080
```
Update `APP_URL` in `.env` to match `http://127.0.0.1:8080`.

### Issue: `SQLSTATE[HY000] [1045] Access denied for user`
**Solution:** Verify your MySQL username and password in `.env`. Ensure MySQL is running via `mysqladmin ping` or the Windows Services panel.

### Issue: `fileinfo` extension warning during document upload
**Solution:** Open your active `php.ini` file, find `;extension=fileinfo`, remove the leading semicolon, and restart your PHP process.

### Issue: Frontend styles or Vite assets not loading
**Solution:** Run `npm run build` to compile static assets directly to `public/build/`.
