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

    public function __construct(array $attributes = [])
    {
        $attributes[$this->dateRangeOptions->startAtField] = $attributes[$this->dateRangeOptions->startAtField]
            ?? $attributes[config('date-range.column_names.date_start_at_key')]
            ?? null;

        $attributes[$this->dateRangeOptions->endAtField] = $attributes[$this->dateRangeOptions->endAtField]
            ?? $attributes[config('date-range.column_names.date_end_at_key')]
            ?? null;

        parent::__construct($attributes);

        $this->fillable[] = $this->dateRangeOptions->startAtField;
        $this->fillable[] = $this->dateRangeOptions->endAtField;
    }

    public function create(array $attributes = [])
    {
        $attributes[$this->dateRangeOptions->startAtField] = $attributes[$this->dateRangeOptions->startAtField]
            ?? $attributes[config('date-range.column_names.date_start_at_key')]
            ?? null;

        $attributes[$this->dateRangeOptions->endAtField] = $attributes[$this->dateRangeOptions->endAtField]
            ?? $attributes[config('date-range.column_names.date_end_at_key')]
            ?? null;

        return static::query()->create($attributes);
    }

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
