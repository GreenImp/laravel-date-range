<?php

namespace GreenImp\DateRange\Contracts;

use Illuminate\Database\Eloquent\Relations\MorphTo;

interface DateRange extends DateRangeable
{
    public function model(): MorphTo;
}
