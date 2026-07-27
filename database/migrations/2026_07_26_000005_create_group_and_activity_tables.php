<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('groups', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('role_id')->nullable()->constrained('roles')->nullOnDelete();
            $table->string('type', 50)->nullable();
            $table->string('name');
            $table->text('description')->nullable();
            $table->boolean('has_special_properties')->default(false);
            $table->boolean('is_active')->default(true);
            $table->boolean('include_in_email_export')->default(false);
            $table->timestamps();
        });

        Schema::create('group_memberships', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('group_id')->constrained('groups')->cascadeOnDelete();
            $table->foreignId('person_id')->constrained('persons')->cascadeOnDelete();
            $table->foreignId('role_id')->nullable()->constrained('roles')->nullOnDelete();
            $table->timestamp('joined_at')->nullable();
            $table->timestamp('left_at')->nullable();
            $table->boolean('is_manager')->default(false);
            $table->timestamps();

            $table->unique(['group_id', 'person_id']);
        });

        Schema::create('events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('group_id')->nullable()->constrained('groups')->nullOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->text('body')->nullable();
            $table->string('location')->nullable();
            $table->dateTime('starts_at')->nullable();
            $table->dateTime('ends_at')->nullable();
            $table->boolean('all_day')->default(false);
            $table->string('calendar_uid')->nullable();
            $table->string('calendar_url')->nullable();
            $table->string('source')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('event_attendances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('event_id')->constrained('events')->cascadeOnDelete();
            $table->foreignId('person_id')->constrained('persons')->cascadeOnDelete();
            $table->timestamp('checked_in_at')->nullable();
            $table->timestamp('checked_out_at')->nullable();
            $table->string('status', 32)->nullable();
            $table->timestamps();

            $table->unique(['event_id', 'person_id']);
        });

        Schema::create('donation_funds', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->boolean('active')->default(true);
            $table->timestamps();

            $table->unique(['tenant_id', 'name']);
        });

        Schema::create('deposits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('fund_id')->nullable()->constrained('donation_funds')->nullOnDelete();
            $table->date('date');
            $table->text('comment')->nullable();
            $table->boolean('closed')->default(false);
            $table->string('type', 32)->nullable();
            $table->foreignId('entered_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('pledges', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('family_id')->constrained('families')->cascadeOnDelete();
            $table->foreignId('fund_id')->nullable()->constrained('donation_funds')->nullOnDelete();
            $table->foreignId('deposit_id')->nullable()->constrained('deposits')->nullOnDelete();
            $table->unsignedSmallInteger('fiscal_year')->nullable();
            $table->date('pledged_on')->nullable();
            $table->decimal('amount', 12, 2)->default(0);
            $table->string('schedule', 32)->nullable();
            $table->string('payment_method', 32)->nullable();
            $table->string('check_number', 64)->nullable();
            $table->string('status', 32)->nullable();
            $table->text('notes')->nullable();
            $table->decimal('non_deductible_amount', 12, 2)->nullable();
            $table->string('payment_type', 32)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pledges');
        Schema::dropIfExists('deposits');
        Schema::dropIfExists('donation_funds');
        Schema::dropIfExists('event_attendances');
        Schema::dropIfExists('events');
        Schema::dropIfExists('group_memberships');
        Schema::dropIfExists('groups');
    }
};
