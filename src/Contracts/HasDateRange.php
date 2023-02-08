<?php

namespace GreenImp\DateRange\Contracts;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;

interface HasDateRange
{
    /**
     * Scope the query to only models where their active date is currently active.
     *
     * @param  Builder  $query
     * @return Builder
     */
    public function scopeIsActive(Builder $query): Builder;

    /**
     * Scope the query to only models where their active date was active on the given date.
     *
     * @param  Builder  $query
     * @param  Carbon  $date
     * @return Builder
     */
    public function scopeIsActiveOn(Builder $query, Carbon $date): Builder;

    /**
     * Scope the query to only models that are currently inactive.
     *
     * @param  Builder  $query
     * @return Builder
     */
    public function scopeIsInactive(Builder $query): Builder;

    /**
     * Scope the query to only models that are inactive on the given date.
     *
     * @param  Builder  $query
     * @param  Carbon  $date
     * @return Builder
     */
    public function scopeIsInactiveOn(Builder $query, Carbon $date): Builder;

    public function scopeOrderByDateRange(Builder $query, string $direction = 'asc'): Builder;

    /**
     * Activate the model, by removing the end date and, if necessary setting a start date.
     *
     * @param  Carbon|null  $date
     * @return bool
     */
    public function activate(?Carbon $date): bool;

    /**
     * De-activate the model by setting its end date.
     *
     * @param  Carbon|null  $date Optional. The end date. If not specified, the current datetime will be used.
     * @return bool
     */
    public function deactivate(?Carbon $date): bool;

    /**
     * Whether the model's dates are currently active or not.
     *
     * @return bool
     */
    public function isActive(): bool;

    /**
     * Return whether the model's dates were active on the given date.
     *
     * @param  Carbon  $date
     * @return bool
     */
    public function isActiveOn(Carbon $date): bool;
}
