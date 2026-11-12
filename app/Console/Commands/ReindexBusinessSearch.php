<?php

namespace App\Console\Commands;

use App\Services\BusinessSearchIndex;
use Illuminate\Console\Command;

class ReindexBusinessSearch extends Command
{
    protected $signature = 'businesses:reindex';

    protected $description = 'Rebuild the directory search index (business names, owners, categories, products and services).';

    public function handle(BusinessSearchIndex $index): int
    {
        $started = microtime(true);
        $count = $index->rebuildAll();
        $this->info(sprintf('Indexed %d businesses in %.2fs.', $count, microtime(true) - $started));

        return self::SUCCESS;
    }
}
