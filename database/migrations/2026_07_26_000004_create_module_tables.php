<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('module_definitions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->nullable()->constrained()->nullOnDelete();
            $table->string('slug')->unique();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('path')->nullable();
            $table->string('version', 32)->nullable();
            $table->boolean('is_core')->default(false);
            $table->boolean('is_enabled')->default(true);
            $table->timestamps();
        });

        Schema::create('module_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('module_definition_id')->constrained('module_definitions')->cascadeOnDelete();
            $table->string('key');
            $table->json('value')->nullable();
            $table->string('type', 32)->nullable();
            $table->boolean('is_secret')->default(false);
            $table->timestamps();

            $table->unique(['tenant_id', 'module_definition_id', 'key'], 'module_settings_scope_key');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('module_settings');
        Schema::dropIfExists('module_definitions');
    }
};
