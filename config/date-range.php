<?php

return [
    'models' => [
        /*
         * When using the "InteractsWithDateRanges" trait from this package, we need to know which
         * Eloquent model should be used to retrieve dates. You may use whatever you like.
         *
         * The model you want to use as a DateRange model needs to implement the
         * `GreenImp\DateRange\Contracts\DateRange` contract.
         */
        'date_range' => \GreenImp\DateRange\Models\DateRange::class,
    ],

    'table_names' => [
        /*
         * When using the "InteractsWithDateRanges" trait from this package, we need to know which
         * table should be used to retrieve your dates. We have chosen a basic
         * default value, but you may easily change it to any table you like.
         */
        'date_ranges' => 'date_ranges',
    ],

    'column_names' => [
        /**
         * The field name used as the ID for the date ranges table.
         */
        'date_range_key' => 'id',

        /**
         * The field names used for the date range start and end dates.
         */
        'date_start_at_key' => 'start_at',
        'date_end_at_key' => 'end_at',

        /**
         * The field name used for the polymorphic ID in the date ranges table.
         */
        'model_morph_key' => 'model_id',
    ],

    /**
     * Setting to false will stop the DB table for `date_ranges` from being created when the migration is run.
     * This can be useful if you'll only be using models with a single start / end date range.
     */
    'multiple_dates' => true,

    /**
     * Set to true if you want models with no start date to be classed as active (ie. if you only want to set an end
     * date).
     */
    'start_optional' => false,
];
