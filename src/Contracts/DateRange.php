<?php

namespace GreenImp\DateRange\Contracts;

use Illuminate\Database\Eloquent\Relations\MorphTo;

interface DateRange extends HasDateRange
{
    public function model(): MorphTo;
}
