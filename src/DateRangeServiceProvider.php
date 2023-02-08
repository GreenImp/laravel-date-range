<?php

namespace GreenImp\DateRange;

use Illuminate\Support\ServiceProvider;

class DateRangeServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->offerPublishing();
    }

    /**
     * Setup the resource publishing groups for the package.
     *
     * @return void
     */
    protected function offerPublishing(): void
    {
        $this->publishes([
            __DIR__.'/../config/date-range.php' => config_path('date-range.php'),
        ], 'config');

        $this->publishes([
            __DIR__.'/../database/migrations/create_date_range_tables.php.stub' => $this->getMigrationFileName('create_date_range_tables.php'),
        ], 'migrations');
    }

    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__.'/../config/date-range.php',
            'date-range',
        );
    }
}
