<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('contacts', function (Blueprint $table) {
            $table->index(['tenant_id', 'person_id']);
            $table->index(['tenant_id', 'family_id']);
            $table->unique(['tenant_id', 'person_id', 'family_id', 'type', 'value'], 'contacts_scope_type_value_unique');
        });
    }

    public function down(): void
    {
        Schema::table('contacts', function (Blueprint $table) {
            $table->dropUnique('contacts_scope_type_value_unique');
            $table->dropIndex(['tenant_id', 'person_id']);
            $table->dropIndex(['tenant_id', 'family_id']);
        });
    }
};
