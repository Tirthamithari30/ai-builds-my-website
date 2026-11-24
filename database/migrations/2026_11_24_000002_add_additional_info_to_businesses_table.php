<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('businesses', function (Blueprint $table) {
            $table->text('additional_info')->nullable()->after('services');
        });
    }

    public function down(): void
    {
        Schema::table('businesses', function (Blueprint $table) {
            $table->dropColumn('additional_info');
        });
    }
};
