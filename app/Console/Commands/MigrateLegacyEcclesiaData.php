<?php

namespace App\Console\Commands;

use App\Support\Legacy\LegacyDataImporter;
use Illuminate\Console\Command;

class MigrateLegacyEcclesiaData extends Command
{
    protected $signature = 'legacy:import {--connection=legacy : The legacy database connection name} {--batch=100 : The number of rows to process at a time}';

    protected $description = 'Import legacy EcclesiaCRM data into the normalized Laravel schema.';

    public function __construct(protected LegacyDataImporter $importer)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        $counts = $this->importer->import(
            connectionName: (string) $this->option('connection'),
            batchSize: (int) $this->option('batch'),
        );

        $this->components->info(sprintf(
            'Imported %d families and %d persons from the legacy source.',
            $counts['families'],
            $counts['persons']
        ));

        return self::SUCCESS;
    }
}
