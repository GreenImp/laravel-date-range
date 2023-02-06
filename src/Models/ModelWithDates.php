<?php

namespace GreenImp\DateRange\Models;

use GreenImp\DateRange\Contracts\HasDateRanges;
use GreenImp\DateRange\InteractsWithDateRanges;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class ModelWithDates extends Model implements HasDateRanges
{
    use InteractsWithDateRanges;
}
