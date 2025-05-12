<?php

namespace App\Providers;

use Filament\Support\Colors\Color;
use Filament\Support\Facades\FilamentColor;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        FilamentColor::register([
            'aset'=>Color::hex('#1c76e2'),
            'kewajiban'=>Color::hex('#ad4128'),
            'equity'=>Color::hex('#3417a5'),
            'penghasilan'=>Color::hex('#ddbe1a'),
            'biaya'=>Color::hex('#ff7f00')
        ]);
    }
}