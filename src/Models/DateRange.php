<?php

namespace GreenImp\DateRange\Models;

use GreenImp\DateRange\Contracts\HasDateRange;
use GreenImp\DateRange\InteractsWithDateRange;
use GreenImp\DateRange\Options\DateRangeOptions;
use Illuminate\Database\Eloquent\Model;

class DateRange extends Model implements HasDateRange
{
    use InteractsWithDateRange;

    public function getTable(): string
    {
        return config('date-range.table_names.date_ranges', parent::getTable());
    }

    public function getKeyName(): string
    {
        return config('date-range.column_names.date_range_key', parent::getKeyName());
    }

    public function getDateRangeOptions(): DateRangeOptions
    {
        return DateRangeOptions::create();
    }
}
