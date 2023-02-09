<?php

namespace GreenImp\DateRange\Models;

use GreenImp\DateRange\Contracts\HasDateRanges;
use GreenImp\DateRange\InteractsWithDateRanges;
use GreenImp\DateRange\Options\DateRangesOptions;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Event extends Model implements HasDateRanges
{
    use InteractsWithDateRanges;

    public function getDateRangesOptions(): DateRangesOptions
    {
        return DateRangesOptions::create();
    }
}
