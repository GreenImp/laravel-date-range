<?php

namespace GreenImp\DateRange\Models;

use GreenImp\DateRange\Contracts\DateRange as DateRangeInterface;
use GreenImp\DateRange\DateRangeOptions;
use GreenImp\DateRange\InteractsWithDateRange;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class DateRange extends Model implements DateRangeInterface
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

    public function model(): MorphTo
    {
        return $this->morphTo();
    }

    public function getDateRangeOptions(): DateRangeOptions
    {
        return DateRangeOptions::create();
    }
}
