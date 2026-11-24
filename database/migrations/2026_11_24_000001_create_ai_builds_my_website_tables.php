<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Businesses Table
        Schema::create('businesses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('name');
            $table->string('category')->default('other'); // restaurant, cafe, salon, retail, consultant, fitness, professional_services, other
            $table->text('description')->nullable();
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('address')->nullable();
            $table->string('city')->nullable();
            $table->string('state')->nullable();
            $table->string('country')->default('India');
            $table->string('postal_code')->nullable();
            $table->json('operating_hours')->nullable();
            $table->json('products')->nullable();
            $table->json('services')->nullable();
            $table->json('social_links')->nullable();
            $table->string('status')->default('draft'); // draft, active, archived
            $table->timestamps();
            $table->softDeletes();

            $table->index(['user_id', 'status']);
        });

        // 2. Business Sources Table (Webpages, URLs, Google Business, social links, search results)
        Schema::create('business_sources', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->constrained('businesses')->cascadeOnDelete();
            $table->string('source_type'); // url, google_business, instagram, facebook, linkedin, search_result, manual
            $table->text('url')->nullable();
            $table->string('title')->nullable();
            $table->longText('raw_content')->nullable();
            $table->string('status')->default('pending'); // pending, crawled, failed, blocked
            $table->text('error_message')->nullable();
            $table->timestamp('crawled_at')->nullable();
            $table->timestamps();

            $table->index(['business_id', 'status']);
        });

        // 3. Uploaded Files Table (PDFs, Images, Brochures, Price lists)
        Schema::create('uploaded_files', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->constrained('businesses')->cascadeOnDelete();
            $table->string('original_name');
            $table->string('file_path');
            $table->string('file_type'); // pdf, image, etc.
            $table->string('mime_type');
            $table->unsignedBigInteger('file_size');
            $table->longText('extracted_text')->nullable();
            $table->string('processing_status')->default('pending'); // pending, processing, completed, failed, scanned_ocr_required
            $table->text('error_message')->nullable();
            $table->timestamps();

            $table->index(['business_id', 'processing_status']);
        });

        // 4. Extracted Facts Table
        Schema::create('extracted_facts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->constrained('businesses')->cascadeOnDelete();
            $table->string('source_type')->default('manual'); // document, webpage, search_result, manual
            $table->unsignedBigInteger('source_id')->nullable();
            $table->text('source_url')->nullable();
            $table->string('fact_key'); // phone, email, address, operating_hours, services, etc.
            $table->string('fact_name'); // Human-readable label
            $table->text('fact_value');
            $table->string('verification_status')->default('extracted'); // extracted, source-supported, user-confirmed, conflicting, unverified, missing
            $table->float('confidence_score')->nullable();
            $table->boolean('is_confirmed')->default(false);
            $table->text('user_confirmed_value')->nullable();
            $table->timestamp('extraction_time')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['business_id', 'fact_key']);
            $table->index(['business_id', 'verification_status']);
        });

        // 5. Fact Confirmations & Conflict Resolutions Table
        Schema::create('fact_confirmations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->constrained('businesses')->cascadeOnDelete();
            $table->string('fact_key');
            $table->text('confirmed_value');
            $table->string('resolution_type')->default('accepted'); // accepted, custom_edited, rejected, conflict_resolved
            $table->json('conflict_candidates')->nullable(); // Previous conflicting options
            $table->foreignId('confirmed_by')->constrained('users')->cascadeOnDelete();
            $table->timestamp('confirmed_at');
            $table->timestamps();

            $table->index(['business_id', 'fact_key']);
        });

        // 6. Websites Table
        Schema::create('websites', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->constrained('businesses')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('title');
            $table->string('slug')->unique();
            $table->string('design_preset')->default('modern_minimal'); // modern_minimal, premium_elegant, bold_creative
            $table->string('primary_color')->default('#2563eb');
            $table->string('secondary_color')->default('#0f172a');
            $table->string('font_family')->default('Inter');
            $table->text('meta_description')->nullable();
            $table->boolean('is_published')->default(false);
            $table->timestamp('published_at')->nullable();
            $table->integer('current_version')->default(1);
            $table->json('settings')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['user_id', 'is_published']);
        });

        // 7. Website Sections Table
        Schema::create('website_sections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('website_id')->constrained('websites')->cascadeOnDelete();
            $table->string('section_type'); // hero, about, services, products, gallery, features, contact, location, faq, cta, footer
            $table->integer('order')->default(0);
            $table->boolean('is_visible')->default(true);
            $table->json('content'); // structured JSON for headings, paragraphs, images, links, etc.
            $table->json('design_overrides')->nullable();
            $table->timestamps();

            $table->index(['website_id', 'order']);
            $table->index(['website_id', 'section_type']);
        });

        // 8. Website Versions Table
        Schema::create('website_versions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('website_id')->constrained('websites')->cascadeOnDelete();
            $table->integer('version_number');
            $table->json('snapshot'); // full snapshot of sections, design preset, colors, settings
            $table->text('changelog')->nullable();
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['website_id', 'version_number']);
        });

        // 9. Publications Table (Audit trail of public deploys and unpublishing)
        Schema::create('publications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('website_id')->constrained('websites')->cascadeOnDelete();
            $table->foreignId('version_id')->nullable()->constrained('website_versions')->nullOnDelete();
            $table->string('published_url');
            $table->foreignId('published_by')->constrained('users')->cascadeOnDelete();
            $table->boolean('is_active')->default(true);
            $table->timestamp('published_at');
            $table->timestamp('unpublished_at')->nullable();
            $table->timestamps();

            $table->index(['website_id', 'is_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('publications');
        Schema::dropIfExists('website_versions');
        Schema::dropIfExists('website_sections');
        Schema::dropIfExists('websites');
        Schema::dropIfExists('fact_confirmations');
        Schema::dropIfExists('extracted_facts');
        Schema::dropIfExists('uploaded_files');
        Schema::dropIfExists('business_sources');
        Schema::dropIfExists('businesses');
    }
};
