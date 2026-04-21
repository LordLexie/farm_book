<?php

namespace App\Providers;

use App\Models\FarmItem;
use App\Models\Service;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        Relation::morphMap([
            'farm_item' => FarmItem::class,
            'service'   => Service::class,
        ]);
    }
}
