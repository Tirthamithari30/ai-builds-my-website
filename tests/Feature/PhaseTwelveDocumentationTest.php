<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\File;
use Tests\TestCase;

class PhaseTwelveDocumentationTest extends TestCase
{
    /**
     * Test that README.md exists and contains all required Phase 12 sections.
     */
    public function test_readme_contains_all_required_sections(): void
    {
        $readmePath = base_path('README.md');
        $this->assertFileExists($readmePath);

        $content = File::get($readmePath);

        $requiredSections = [
            'Problem Statement',
            'Project Objectives',
            'Core Value Proposition & Golden Rules',
            'System Architecture',
            'Key Features by Phase',
            'Technology Stack',
            'Database Schema & Architecture',
            'API & Route Reference',
            'Prerequisites & Requirements',
            'Installation & Local Setup',
            'Configuration Guide',
            'Database Setup & Migrations',
            'Running the Application',
            'Gemini AI Integration Architecture',
            'Automated Test Suite',
            'Security & Defensive Hardening',
            'Deployment Instructions',
            'Known Limitations',
            'Future Scope',
            'Repository Structure',
        ];

        foreach ($requiredSections as $section) {
            $this->assertStringContainsString($section, $content, "README.md must contain section: '{$section}'");
        }
    }

    /**
     * Test that dedicated DATABASE.md exists and documents core domain tables.
     */
    public function test_database_documentation_exists_and_covers_tables(): void
    {
        $dbDocPath = base_path('DATABASE.md');
        $this->assertFileExists($dbDocPath);

        $content = File::get($dbDocPath);

        $tables = [
            'users',
            'businesses',
            'business_sources',
            'uploaded_files',
            'extracted_facts',
            'fact_confirmations',
            'websites',
            'website_sections',
            'website_versions',
            'publications',
        ];

        foreach ($tables as $table) {
            $this->assertStringContainsString($table, $content, "DATABASE.md must document table: '{$table}'");
        }
    }

    /**
     * Test that dedicated API.md exists and covers route modules.
     */
    public function test_api_documentation_exists_and_covers_endpoints(): void
    {
        $apiDocPath = base_path('API.md');
        $this->assertFileExists($apiDocPath);

        $content = File::get($apiDocPath);

        $endpoints = [
            '/sites/{slug}',
            '/login',
            '/register',
            '/dashboard',
            '/businesses',
            '/sources',
            '/facts',
            '/website/generate',
            '/sections',
            '/publish',
            '/seo',
        ];

        foreach ($endpoints as $endpoint) {
            $this->assertStringContainsString($endpoint, $content, "API.md must document endpoint: '{$endpoint}'");
        }
    }

    /**
     * Test that .env.example contains sanitized placeholders with zero real credentials.
     */
    public function test_env_example_contains_no_real_credentials(): void
    {
        $envExamplePath = base_path('.env.example');
        $this->assertFileExists($envExamplePath);

        $content = File::get($envExamplePath);

        // Check required keys exist
        $this->assertStringContainsString('APP_NAME=', $content);
        $this->assertStringContainsString('DB_CONNECTION=', $content);
        $this->assertStringContainsString('GEMINI_API_KEY=', $content);
        $this->assertStringContainsString('SEARCH_PROVIDER=', $content);

        // Ensure no real secrets or production keys are present
        $this->assertStringNotContainsString('AIzaSy', $content, 'Real Google API keys must never appear in .env.example.');
        $this->assertStringContainsString('your_gemini_api_key_here', $content);
        $this->assertStringContainsString('your_search_api_key_here', $content);
    }

    /**
     * Test that supporting project documentation files exist.
     */
    public function test_supporting_documentation_files_exist_and_are_populated(): void
    {
        $files = [
            'DEPLOYMENT.md',
            'DECISIONS.md',
            'DEMO_SCRIPT.md',
            'SUBMISSION_EMAIL.md',
            '.env.production.example',
        ];

        foreach ($files as $file) {
            $path = base_path($file);
            $this->assertFileExists($path, "Documentation file '{$file}' must exist.");
            $this->assertGreaterThan(50, File::size($path), "Documentation file '{$file}' must not be empty.");
        }
    }
}
