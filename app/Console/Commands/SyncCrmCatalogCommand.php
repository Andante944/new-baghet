<?php

namespace App\Console\Commands;

use App\Services\CrmCatalogSync;
use Illuminate\Console\Command;

class SyncCrmCatalogCommand extends Command
{
    protected $signature = 'baghet:sync-crm-catalog';

    protected $description = 'Sync bagheta names and sale prices from etichete-crm by sku';

    public function handle(CrmCatalogSync $sync): int
    {
        $this->info('Syncing catalog from etichete-crm…');

        try {
            $stats = $sync->sync();
        } catch (\Throwable $e) {
            $this->error($e->getMessage());

            return self::FAILURE;
        }

        $this->table(
            ['Matched', 'Updated', 'Unbound local', 'CRM-only sku'],
            [[$stats['matched'], $stats['updated'], $stats['unbound'], $stats['missing_local']]]
        );

        return self::SUCCESS;
    }
}
