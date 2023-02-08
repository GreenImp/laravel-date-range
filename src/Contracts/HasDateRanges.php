<?php

namespace GreenImp\DateRange\Contracts;

use Illuminate\Database\Eloquent\Relations\MorphMany;

interface HasDateRanges extends HasDateRange
{
    /**
     * The date ranges for the model.
     *
     * @return MorphMany
     */
    public function dates(): MorphMany;
}
