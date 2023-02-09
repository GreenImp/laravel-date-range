<?php

namespace GreenImp\DateRange\Options;

use GreenImp\DateRange\Contracts\HasDateRanges;
use Illuminate\Support\Str;

class DateRangeOptions
{
    public ?string $parent = null;

    public function __construct(
        public string             $startAtField,
        public string             $endAtField,
        public bool               $startOptional,
        public string             $foreignKeyName,
        public bool               $polymorphic,
        string|HasDateRanges|null $parent = null,
    )
    {
        if ($parent instanceof HasDateRanges) {
            $parent = $parent->getMorphClass();
        }

        $this->parent = $parent;
    }

    public static function create(
        ?string                   $startField = null,
        ?string                   $endAtField = null,
        ?bool                     $startOptional = null,
        ?string                   $foreignKeyName = null,
        ?bool                     $polymorphic = null,
        string|HasDateRanges|null $parent = null,
    ): static
    {
        return new static(
            $startField ?? config('date-range.column_names.date_start_at_key'),
            $endAtField ?? config('date-range.column_names.date_end_at_key'),
            $startOptional ?? config('date-range.start_optional', false),
            $foreignKeyName
                ?? config('date-range.relationship.foreign_key_name')
                ?? Str::singular(config('date-range.relationship.name_on_child')),
            $polymorphic ?? config('date-range.relationship.polymorphic', true),
            $parent ?? config('date-range.models.parent_model'),
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

    public function polymorphic(bool $bool = true): self
    {
        $this->polymorphic = $bool;

        return $this;
    }

    public function foreignKeyName(string $name): self
    {
        $this->foreignKeyName = $name;

        return $this;
    }

    public function parent(string|HasDateRanges $model): self
    {
        $this->parent = $model instanceof HasDateRanges ? $model->getMorphClass() : $model;

        return $this;
    }
}
