<?php

namespace App\Services\Search\Engines;

use Illuminate\Database\Eloquent\Builder;

interface ProductSearchEngine
{
    public function query(string $query): Builder;
}
