<?php

namespace GreenImp\DateRange\Options;

use GreenImp\DateRange\Contracts\HasDateRange;
use Illuminate\Support\Str;

class DateRangesOptions
{
    public string $dateRangeModel;

    public function __construct(
        string|HasDateRange $dateRangeModel,
        public string $foreignKeyName,
        public bool $polymorphic = true,
    )
    {
        if ($dateRangeModel instanceof HasDateRange) {
            $dateRangeModel = $dateRangeModel->getMorphClass();
        }

        $this->dateRangeModel = $dateRangeModel;
    }

    public static function create(
        string|HasDateRange|null $dateRangeModel = null,
        ?string $foreignKeyName = null,
        ?bool $polymorphic = null,
    ): static
    {
        return new static(
            $dateRangeModel ?? config('date-range.models.date_range'),
            $foreignKeyName
                ?? config('date-range.relationship.foreign_key_name')
                ?? Str::singular(config('date-range.relationship.name_on_child')),
            $polymorphic ?? config('date-range.relationship.polymorphic', true),
        );
    }

    public function dateRangeModel(string|HasDateRange $model): self
    {
        $this->dateRangeModel = $model instanceof HasDateRange ? $model->getMorphClass() : $model;

        return $this;
    }

    public function foreignKeyName(string $name): self
    {
        $this->foreignKeyName = $name;

        return $this;
    }

    public function polymorphic(bool $bool = true): self
    {
        $this->polymorphic = $bool;

        return $this;
    }
}
