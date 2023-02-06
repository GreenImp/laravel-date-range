<?php

namespace GreenImp\DateRange\Contracts;

use Illuminate\Database\Eloquent\Relations\MorphMany;

interface HasDateRanges extends DateRangeable
{
    /**
     * The date ranges for the model.
     *
     * @return MorphMany
     */
    public function dates(): MorphMany;
}
