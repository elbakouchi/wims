<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Http\Resources\Json\JsonResource;

class AppServiceProvider extends ServiceProvider
{
    public function boot()
    {
        JsonResource::withoutWrapping();
    }

    public function register()
    {
        $this->app->extend(
            \Illuminate\Translation\Translator::class,
            fn ($translator) => new \App\Helpers\Translator($translator->getLoader(), $translator->getLocale())
        );

        $this->app->extend(\Illuminate\Pagination\LengthAwarePaginator::class, fn ($paginator) => $paginator->onEachSide(2));
    }
}
