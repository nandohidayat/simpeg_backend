<?php

namespace App\Providers;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        //
        Schema::defaultStringLength(191);
        Blade::directive('money', function ($amount) {
            return "<?php echo 'Rp ' . number_format(intval($amount), 2); ?>";
        });
        Blade::directive('percent', function ($amount) {
            return "<?php echo sprintf(\"%.1f %%\", intval($amount)); ?>";
        });
    }
}
