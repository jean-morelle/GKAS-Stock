<?php

namespace App\Providers;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Route;
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
        Carbon::setLocale(config('app.locale'));

        // Détecte les requêtes N+1 et attributs inexistants pendant le développement.
        Model::shouldBeStrict(! $this->app->isProduction());

        // {technician} ne résout que des utilisateurs ayant le rôle technicien.
        Route::bind('technician', fn (string $value) => User::technicians()->findOrFail($value));
    }
}
