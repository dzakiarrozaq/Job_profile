<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View; 
use App\Models\TrainingPlan;
use Illuminate\Support\Facades\Auth;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {

    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        View::composer('*', function ($view) {
            $rencanaCount = 0;

            if (Auth::check()) {
                $rencanaCount = TrainingPlan::where('user_id', Auth::id())
                                    ->where('status', 'draft') 
                                    ->count();
            }

            $view->with('rencanaCount', $rencanaCount);
        });
    }
}