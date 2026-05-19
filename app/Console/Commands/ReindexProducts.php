<?php

namespace App\Console\Commands;

use App\Models\Product;
use App\Services\Search\ProductSearchIndexer;
use Illuminate\Console\Command;

class ReindexProducts extends Command
{
    protected $signature = 'products:reindex {--id= : Only reindex a single product id} {--chunk=200 : Chunk size}';

    protected $description = 'Rebuild products search_text FULLTEXT index';

    public function handle(ProductSearchIndexer $indexer): int
    {
        $id = $this->option('id');
        $chunk = (int) ($this->option('chunk') ?: 200);
        if ($chunk < 1) {
            $chunk = 200;
        }

        if ($id !== null && $id !== '') {
            $product = Product::query()->find((int) $id);
            if (!$product) {
                $this->error('Product not found: ' . $id);
                return 1;
            }

            $indexer->rebuild($product);
            $this->info('Reindexed product: ' . $product->id);
            return 0;
        }

        $processed = 0;

        Product::query()
            ->select(['id'])
            ->orderBy('id')
            ->chunkById($chunk, function ($rows) use (&$processed, $indexer) {
                $ids = $rows->pluck('id')->all();

                $products = Product::query()
                    ->whereIn('id', $ids)
                    ->get();

                foreach ($products as $product) {
                    $indexer->rebuild($product);
                    $processed++;
                }

                $this->line('Processed: ' . $processed);
            });

        $this->info('Reindex finished. Total: ' . $processed);

        return 0;
    }
}
