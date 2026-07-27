<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class LegacyImportTest extends TestCase
{
    use RefreshDatabase;

    public function test_legacy_import_backfills_core_records_idempotently(): void
    {
        $legacyPath = storage_path('framework/testing/legacy-import.sqlite');
        File::ensureDirectoryExists(dirname($legacyPath));
        File::put($legacyPath, '');

        config()->set('database.connections.legacy', [
            'driver' => 'sqlite',
            'database' => $legacyPath,
            'prefix' => '',
            'foreign_key_constraints' => false,
            'busy_timeout' => null,
            'journal_mode' => null,
            'synchronous' => null,
            'transaction_mode' => 'DEFERRED',
        ]);

        DB::purge('legacy');
        DB::connection('legacy')->getPdo();

        Schema::connection('legacy')->create('family_fam', function ($table) {
            $table->integer('fam_ID')->primary();
            $table->string('fam_Name');
            $table->string('fam_Address1')->nullable();
            $table->string('fam_City')->nullable();
            $table->string('fam_State')->nullable();
            $table->string('fam_Zip')->nullable();
            $table->string('fam_Country')->nullable();
            $table->string('fam_Email')->nullable();
            $table->date('fam_WeddingDate')->nullable();
            $table->timestamp('fam_DateEntered')->nullable();
            $table->timestamp('fam_DateLastEdited')->nullable();
        });

        Schema::connection('legacy')->create('person_per', function ($table) {
            $table->integer('per_ID')->primary();
            $table->string('per_FirstName');
            $table->string('per_LastName');
            $table->integer('per_fam_ID')->nullable();
            $table->string('per_Email')->nullable();
            $table->string('per_CellPhone')->nullable();
            $table->tinyInteger('per_Gender')->nullable();
            $table->date('per_MembershipDate')->nullable();
            $table->timestamp('per_DateEntered')->nullable();
            $table->timestamp('per_DateLastEdited')->nullable();
        });

        DB::connection('legacy')->table('family_fam')->insert([
            'fam_ID' => 1,
            'fam_Name' => 'Doe Family',
            'fam_Address1' => '123 Main St',
            'fam_City' => 'Fortaleza',
            'fam_State' => 'CE',
            'fam_Zip' => '60000-000',
            'fam_Country' => 'BR',
            'fam_Email' => 'family@example.com',
            'fam_WeddingDate' => null,
            'fam_DateEntered' => now(),
            'fam_DateLastEdited' => now(),
        ]);

        DB::connection('legacy')->table('person_per')->insert([
            'per_ID' => 1,
            'per_FirstName' => 'John',
            'per_LastName' => 'Doe',
            'per_fam_ID' => 1,
            'per_Email' => 'john@example.com',
            'per_CellPhone' => '(85) 99999-0000',
            'per_Gender' => 1,
            'per_MembershipDate' => null,
            'per_DateEntered' => now(),
            'per_DateLastEdited' => now(),
        ]);

        $this->artisan('legacy:import --connection=legacy')
            ->assertExitCode(0);

        $this->assertDatabaseHas('tenants', ['slug' => 'default']);
        $this->assertDatabaseHas('users', ['email' => 'admin@localhost']);
        $this->assertDatabaseHas('families', ['name' => 'Doe Family']);
        $this->assertDatabaseHas('persons', ['first_name' => 'John', 'last_name' => 'Doe']);

        $familyCount = DB::table('families')->count();
        $personCount = DB::table('persons')->count();

        $this->artisan('legacy:import --connection=legacy')
            ->assertExitCode(0);

        $this->assertSame($familyCount, DB::table('families')->count());
        $this->assertSame($personCount, DB::table('persons')->count());
    }
}
