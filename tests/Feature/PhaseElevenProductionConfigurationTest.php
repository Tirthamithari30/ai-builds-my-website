<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\View;
use Tests\TestCase;

class PhaseElevenProductionConfigurationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that production security headers are appended to HTTP responses.
     */
    public function test_security_headers_middleware_is_applied_to_web_routes(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertHeader('X-Content-Type-Options', 'nosniff');
        $response->assertHeader('X-Frame-Options', 'SAMEORIGIN');
        $response->assertHeader('X-XSS-Protection', '1; mode=block');
        $response->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->assertHeader('Permissions-Policy', 'camera=(), microphone=(), geolocation=()');
    }

    /**
     * Test that services.php explicitly defines gemini and search configuration
     * so that php artisan config:cache preserves them without losing env values.
     */
    public function test_production_services_config_defines_gemini_and_search_keys(): void
    {
        $services = config('services');

        $this->assertArrayHasKey('gemini', $services, 'services.gemini configuration key must be defined.');
        $this->assertArrayHasKey('api_key', $services['gemini']);
        $this->assertArrayHasKey('model', $services['gemini']);

        $this->assertArrayHasKey('search', $services, 'services.search configuration key must be defined.');
        $this->assertArrayHasKey('provider', $services['search']);
        $this->assertArrayHasKey('api_key', $services['search']);
    }

    /**
     * Test that branded error views exist and can render without errors.
     */
    public function test_production_error_views_exist_and_render_cleanly(): void
    {
        $this->assertTrue(View::exists('errors.404'), 'View errors.404 must exist.');
        $this->assertTrue(View::exists('errors.403'), 'View errors.403 must exist.');
        $this->assertTrue(View::exists('errors.500'), 'View errors.500 must exist.');

        $html404 = view('errors.404')->render();
        $this->assertStringContainsString('404 Error', $html404);
        $this->assertStringContainsString('Page Not Found', $html404);

        $html403 = view('errors.403')->render();
        $this->assertStringContainsString('403 Forbidden', $html403);
        $this->assertStringContainsString('Access Denied', $html403);

        $html500 = view('errors.500')->render();
        $this->assertStringContainsString('500 Server Error', $html500);
        $this->assertStringContainsString('Something Went Wrong', $html500);
    }

    /**
     * Test that the /up health check route responds successfully.
     */
    public function test_production_health_check_endpoint_returns_ok(): void
    {
        $response = $this->get('/up');
        $response->assertSuccessful();
    }

    /**
     * Test that database configuration adheres to production standards (strict mode & utf8mb4).
     */
    public function test_production_database_config_has_strict_mode_and_utf8mb4(): void
    {
        $mysql = config('database.connections.mysql');

        $this->assertTrue($mysql['strict'], 'MySQL strict mode must be enabled.');
        $this->assertSame('utf8mb4', $mysql['charset'], 'MySQL charset must be utf8mb4.');
        $this->assertSame('utf8mb4_unicode_ci', $mysql['collation'], 'MySQL collation must be utf8mb4_unicode_ci.');
        $this->assertArrayHasKey('options', $mysql, 'MySQL connection options must be configured.');
    }

    /**
     * Test that filesystem configuration includes public disk and storage link mapping.
     */
    public function test_public_storage_symlink_target_and_disk_configuration(): void
    {
        $publicDisk = config('filesystems.disks.public');

        $this->assertNotNull($publicDisk);
        $this->assertSame('local', $publicDisk['driver']);
        $this->assertSame('public', $publicDisk['visibility']);

        $links = config('filesystems.links');
        $this->assertArrayHasKey(public_path('storage'), $links);
    }

    /**
     * Test that backup, restore, and deploy automation scripts exist.
     */
    public function test_backup_and_deployment_scripts_exist(): void
    {
        $this->assertFileExists(base_path('scripts/backup-db.sh'));
        $this->assertFileExists(base_path('scripts/restore-db.sh'));
        $this->assertFileExists(base_path('scripts/deploy.sh'));

        $backupContent = File::get(base_path('scripts/backup-db.sh'));
        $this->assertStringContainsString('mysqldump', $backupContent);
        $this->assertStringContainsString('gzip', $backupContent);

        $deployContent = File::get(base_path('scripts/deploy.sh'));
        $this->assertStringContainsString('composer install', $deployContent);
        $this->assertStringContainsString('php artisan migrate', $deployContent);
        $this->assertStringContainsString('php artisan config:cache', $deployContent);
    }

    /**
     * Test that production environment template specifies production settings.
     */
    public function test_production_environment_example_exists_and_specifies_production_settings(): void
    {
        $this->assertFileExists(base_path('.env.production.example'));
        $content = File::get(base_path('.env.production.example'));

        $this->assertStringContainsString('APP_ENV=production', $content);
        $this->assertStringContainsString('APP_DEBUG=false', $content);
        $this->assertStringContainsString('SESSION_DRIVER=database', $content);
        $this->assertStringContainsString('SESSION_SECURE_COOKIE=true', $content);
        $this->assertStringContainsString('LOG_CHANNEL=daily', $content);
    }

    /**
     * Test that DEPLOYMENT.md documentation exists and covers all operations.
     */
    public function test_deployment_documentation_exists_and_covers_operations(): void
    {
        $this->assertFileExists(base_path('DEPLOYMENT.md'));
        $content = File::get(base_path('DEPLOYMENT.md'));

        $this->assertStringContainsString('Nginx', $content);
        $this->assertStringContainsString('PHP 8.4', $content);
        $this->assertStringContainsString('backup-db.sh', $content);
        $this->assertStringContainsString('restore-db.sh', $content);
        $this->assertStringContainsString('deploy.sh', $content);
        $this->assertStringContainsString('Certbot', $content);
    }
}
