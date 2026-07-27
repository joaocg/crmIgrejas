<?php

namespace Database\Seeders;

use App\Support\Legacy\LegacyDataImporter;
use Illuminate\Database\Seeder;
use RuntimeException;

class LegacyDataSeeder extends Seeder
{
    public function run(): void
    {
        if (! array_key_exists('legacy', config('database.connections', []))) {
            return;
        }

        try {
            app(LegacyDataImporter::class)->import('legacy');
        } catch (RuntimeException) {
            return;
        }
    }
}
