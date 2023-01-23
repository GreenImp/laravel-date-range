<?php

namespace GreenImp\DateRange;

class DateRangeOptions
{
    public function __construct(public string $startAtField, public string $endAtField, public bool $startOptional = false)
    {
        //
    }

    public function create(string $startField = 'start_at', string $endAtField = 'end_at', bool $startOptional = false): static
    {
        return new static($startField, $endAtField, $startOptional);
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

    public function startOptional(bool $optional): self
    {
        $this->startOptional = $optional;

        return $this;
    }
}
