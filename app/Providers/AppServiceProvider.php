<?php

namespace App\Providers;

use Illuminate\Support\Carbon;
use Illuminate\Support\ServiceProvider;
use App\Models\Pengaturan;
use Laravel\Passport\Passport;

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
        Carbon::setLocale('id');

        // Dynamic app name from settings, fallback to config/app.php value
        try {
            $appNama = Pengaturan::dapatkan('app_nama', config('app.name'));
            config(['app.name' => $appNama]);
        } catch (\Illuminate\Database\QueryException $e) {
            // Table pengaturans belum ada — gunakan default dari config
        }
    }
}
