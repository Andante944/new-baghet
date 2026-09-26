<?php

namespace App\Console\Commands;

use App\Services\BaghetScraper;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

class ScrapeBaghetCommand extends Command
{
    protected $signature = 'baghet:scrape
        {--only= : plastic|hardware}
        {--limit= : Max products per category}
        {--no-images : Skip image download}';

    protected $description = 'Import plastic molding and hardware products from baghet.md';

    public function handle(BaghetScraper $scraper): int
    {
        $only = $this->option('only');
        if ($only && ! in_array($only, ['plastic', 'hardware'], true)) {
            $this->error('Option --only must be plastic or hardware');

            return self::FAILURE;
        }

        $limit = $this->option('limit');
        $limit = $limit !== null ? (int) $limit : null;

        $this->info('Scraping baghet.md…');
        $stats = $scraper->scrape(
            only: $only,
            limit: $limit,
            withImages: ! $this->option('no-images'),
        );

        $this->table(
            ['Imported', 'Updated', 'Failed'],
            [[$stats['imported'], $stats['updated'], $stats['failed']]]
        );

        Cache::forget('sitemap.xml');

        return $stats['failed'] > 0 && ($stats['imported'] + $stats['updated']) === 0
            ? self::FAILURE
            : self::SUCCESS;
    }
}