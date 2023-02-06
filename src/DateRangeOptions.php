<?php

namespace GreenImp\DateRange;

class DateRangeOptions
{
    public function __construct(public string $startAtField, public string $endAtField, public bool $startOptional = false)
    {
        //
    }

    public static function create(?string $startField = null, ?string $endAtField = null, ?bool $startOptional = null): static
    {
        return new static(
            $startField ?? config('date-range.column_names.date_start_at_key'),
            $endAtField ?? config('date-range.column_names.date_end_at_key'),
            $startOptional ?? config('date-range.start_optional', false)
        );
    }

    public function startAtField(string $field): self
    {
        $this->startAtField = $field;

        return $this;
    }

    public function endAtField(string $field): self
    {
        $this->endAtField = $field;

        return $this;
    }

    public function startOptional(bool $optional = true): self
    {
        $this->startOptional = $optional;

        return $this;
    }

    public function startRequired(): self
    {
        return $this->startOptional(false);
    }
}
