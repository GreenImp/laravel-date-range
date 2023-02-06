<?php

namespace GreenImp\DateRange;

use Illuminate\Support\ServiceProvider;

class DateRangeServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->offerPublishing();
    }

    /**
     * Setup the resource publishing groups for the package.
     *
     * @return void
     */
    protected function offerPublishing()
    {
        if (! function_exists('config_path')) {
            // function not available and 'publish' not relevant in Lumen
            return;
        }

        $this->publishes([
            __DIR__.'/../config/date-range.php' => config_path('date-range.php'),
        ], 'date-range-config');
    }

    public function register()
    {
        $this->mergeConfigFrom(
            __DIR__.'/../config/date-range.php',
            'date-range',
        );

        $this->publishes([
            __DIR__.'/../database/migrations/create_date_range_tables.php.stub' => $this->getMigrationFileName('create_date_range_tables.php'),
        ], 'date-range-migrations');
    }
}
