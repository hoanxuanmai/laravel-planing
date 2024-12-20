<?php

namespace HXM\LaravelPlanning\Traits\Concerns;

use \Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;

trait HasItems
{

    function getItems(): Collection
    {
        return $this->items->loadMissing(['condition', 'percent_price']);
    }

    /**
     * Laravel Relations
     * @return HasMany
     */
    abstract public function items(): HasMany;
}
