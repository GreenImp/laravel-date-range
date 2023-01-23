<?php

namespace GreenImp\DateRange\Concerns;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Str;

/**
 * Trait HasDateRange.
 *
 * @property-read bool start_optional Whether an empty value for the start date means that the model has started.
 * @property Carbon|null end_date Generic way of getting the end date value.
 * @property-read bool has_ended Whether the model's date range has ended or not.
 * @property-read bool has_started Whether the model's date range has started or not.
 * @property-read bool is_active Whether the model's dates are currently active or not.
 * @property Carbon|null start_date Generic way of getting the start date value.
 */
trait HasDateRange
{
    /* Scopes */

    /**
     * Changes the query to an `UPDATE` query and activates the queried rows.
     *
     * This sets their start date to the specified `$date` or the current date, if not defined.
     *
     * @param  Builder  $query
     * @param  Carbon|null  $date The date to mark the models as activated (Defaults to "now").
     * @return int
     */
    public function scopeActivate(Builder $query, ?Carbon $date = null): int
    {
        return $query->get()->each->activate($date);
    }

    /**
     * Scope the query to only models where their active date is currently active.
     *
     * @param  Builder  $query
     * @return Builder
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->activeOn(Carbon::now());
    }

    /**
     * Scope the query to only models where their active date was active on the given date.
     *
     * This means that the "from" date started on or before the date, and the "to" date is either
     * not set (model still active) or is set to a date after the given date (Ended after the given date).
     *
     * @param  Builder  $query
     * @param  Carbon  $to
     * @return Builder
     */
    public function scopeActiveOn(Builder $query, Carbon $to): Builder
    {
        return $query
            ->startedByDate($to)
            ->hasNotEndedBy($to);
    }

    /**
     * Scope the query to only models where their active date range falls within or overlaps
     * the given from and to range.
     *
     * This will include any models where their from / to dates start / end outside the range
     * if those dates overlap any dates within the range.
     *
     * @param  Builder  $query
     * @param  Carbon  $from
     * @param  Carbon  $to
     * @return Builder
     *
     * @see {@link scopeDateWithin} to only include results where both dates are within the range.
     */
    public function scopeDateBetween(Builder $query, Carbon $from, Carbon $to): Builder
    {
        return $query
            // ensure "from" date is before or equal to the range end
            ->startedByDate($to)
            ->where(function (Builder $query) use ($from, $to) {
                // check if "from date" is after or equal to range start
                $query->startedOnOrAfter($from);

                // or the "to date" is within the range
                $query->orWhere(function (Builder $query) use ($from, $to) {
                    $query->endedBetween($from, $to);
                });

                // or there is no "end date"
                $query->orWhere(function (Builder $query) {
                    $query->doesntHaveEnd();
                });
            });
    }

    /**
     * Scope the query to only models where their active date falls within the given from and to range.
     *
     * This will exclude any models where their from / to dates fall outside the range,
     * even if they overlap with it.
     *
     * @param  Builder  $query
     * @param  Carbon  $from
     * @param  Carbon  $to
     * @return Builder
     *
     * @see {@link scopeDateBetween} to include results that overlap the date range.
     */
    public function scopeDateWithin(Builder $query, Carbon $from, Carbon $to): Builder
    {
        // date from is within the range and date to is either null or also within the range
        return $query
            ->startedBetween($from, $to)
            ->endedBetween($from, $to);
    }

    /**
     * Changes the query to an `UPDATE` query and de-activates the queried rows.
     *
     * This sets their end date to the specified `$date` or the current date, if not defined.
     *
     * @param  Builder  $query
     * @param  Carbon|null  $date The date to mark the models as deactivated (Defaults to "now").
     * @return Collection
     */
    public function scopeDeactivate(Builder $query, ?Carbon $date = null): Collection
    {
        return $query->get()->each->deactivate($date);
    }

    /**
     * Scope the query to only models that have no end date.
     *
     * @param  Builder  $query
     * @return Builder
     */
    public function scopeDoesntHaveEnd(Builder $query): Builder
    {
        return $query->whereNull($this->qualifyColumn($this->getEndDateName()));
    }

    /**
     * Scope the query to only models that have no start date.
     *
     * @param  Builder  $query
     * @return Builder
     */
    public function scopeDoesntHaveStart(Builder $query): Builder
    {
        return $query->whereNull($this->qualifyColumn($this->getStartDateName()));
    }

    /**
     * Scope the query to only models where their active date has ended.
     *
     * @param  Builder  $query
     * @return Builder
     */
    public function scopeEnded(Builder $query): Builder
    {
        return $query->endedBy(Carbon::now());
    }

    /**
     * Scope the query to only return models where their active date ended within the given range.
     *
     * @param  Builder  $query
     * @param  Carbon  $from
     * @param  Carbon  $to
     * @return Builder
     */
    public function scopeEndedBetween(Builder $query, Carbon $from, Carbon $to): Builder
    {
        return $query->hasEnd()
            ->whereBetween($this->getEndDateName(), [$from->startOfDay(), $to->endOfDay()]);
    }

    /**
     * Scope the query to only models where their active date ended on or before the given to date.
     *
     * @param  Builder  $query
     * @param  Carbon  $to
     * @return Builder
     */
    public function scopeEndedBy(Builder $query, Carbon $to): Builder
    {
        return $query->hasEnd()
            ->where($this->qualifyColumn($this->getEndDateName()), '<=', $to);
    }

    /**
     * Scope the models to only those that have a "to" date.
     *
     * Realistically, this should return the same as the `ended` scope, unless a models
     * has an end date in the future.
     *
     * This scope will return results, even if the end date is in the future. The `ended` scope
     * will only return results that have already ended.
     *
     * @param  Builder  $query
     * @return Builder
     *
     * @see {@link scopeEnded} to only return results that have ended.
     */
    public function scopeHasEnd(Builder $query): Builder
    {
        return $query->whereNotNull($this->qualifyColumn($this->getEndDateName()));
    }

    /**
     * Scope the query to only models that have **not** ended.
     *
     * @param  Builder  $query
     * @return Builder
     */
    public function scopeHasNotEnded(Builder $query): Builder
    {
        return $query->hasNotEndedBy(Carbon::now());
    }

    /**
     * Scope the query to only models that have **not** ended by the given date.
     *
     * @param  Builder  $query
     * @param  Carbon  $date
     * @return Builder
     */
    public function scopeHasNotEndedBy(Builder $query, Carbon $date): Builder
    {
        // No end date, or end date is after the $date
        return $query->where(function (Builder $query) use ($date) {
            $query->doesntHaveEnd()
                ->orWhere($this->qualifyColumn($this->getEndDateName()), '>', $date);
        });
    }

    /**
     * Scope the models to only those that have a "from" date.
     *
     * This should realistically be all of them.
     *
     * @param  Builder  $query
     * @return Builder
     */
    public function scopeHasStart(Builder $query): Builder
    {
        return $query->whereNotNull($this->qualifyColumn($this->getStartDateName()));
    }

    /**
     * Scope the query to only models that have **not** started.
     *
     * @param  Builder  $query
     * @param  Carbon  $date
     * @return Builder
     */
    public function scopeHasntStarted(Builder $query): Builder
    {
        return $query->hasntStartedBy(Carbon::now());
    }

    /**
     * Scope the query to only models that have **not** started by the given date.
     *
     * @param  Builder  $query
     * @param  Carbon  $date
     * @return Builder
     */
    public function scopeHasntStartedBy(Builder $query, Carbon $date): Builder
    {
        // if start is optional then modal hasn't started only if it HAS a start date, and it's in the future
        if ($this->start_optional) {
            return $query->hasStart()
                ->where($this->qualifyColumn($this->getStartDateName()), '>', $date);
        }

        // No start date or start date is after the $date
        return $query->where(function (Builder $query) use ($date) {
            return $query
                ->doesntHaveStart()
                ->orWhere($this->qualifyColumn($this->getStartDateName()), '>', $date);
        });
    }

    /**
     * Scope the query to only models that are currently inactive.
     *
     * This could be because they've not started yet, or because they've already ended.
     *
     * @param  Builder  $query
     * @return Builder
     *
     * @see {@link scopeHasntStarted} to only check if it hasn't started yet.
     * @see {@link scopeEnded} to only check if it's already ended.
     */
    public function scopeInactive(Builder $query): Builder
    {
        // model is inactive if it hasn't started yet (No start, or start in the future)
        // or if it's already ended
        return $query->where(function (Builder $query) {
            return $query
                ->hasntStarted()
                ->orWhere(function (Builder $query) {
                    return $query->ended();
                });
        });
    }

    /**
     * Scope teh query to only models that have started.
     *
     * Note: this could include models that have also ended. To check for only active models, use the
     * {@link scopeActive} scope.
     *
     * @param  Builder  $query
     * @return Builder
     *
     * @scope {@link scopeActive} to check for models that have started and **not** ended.
     */
    public function scopeStarted(Builder $query): Builder
    {
        return $query->startedByDate(Carbon::now());
    }

    /**
     * Scope the query to only models where their active date started on or before the date.
     *
     * This will return both active and inactive models, as it only checks that the "from"
     * date is before the given date, and ignores and "to" date.
     *
     * @param  Builder  $query
     * @param  Carbon  $date
     * @return Builder
     *
     * @see {@link HasDateRange::scopeActiveOn} To only return models than have started by the date, and have not ended by it.
     * @see {@link HasDateRange::scopeEndedBy} For the opposite of this.
     */
    public function scopeStartedByDate(Builder $query, Carbon $date): Builder
    {
        // if start is optional then model has started if; no start date, or start date is <= $date
        if ($this->start_optional) {
            return $query->where(function (Builder $query) use ($date) {
                $query->doesntHaveStart()
                    ->orWhere($this->qualifyColumn($this->getStartDateName()), '<=', $date);
            });
        }

        // if start is NOT optional, then we just compare the datetime
        return $query->hasStart()
            ->where($this->qualifyColumn($this->getStartDateName()), '<=', $date);
    }

    /**
     * Scope te query to only models where their active date started within the given range.
     *
     * @param  Builder  $query
     * @param  Carbon  $from
     * @param  Carbon  $to
     * @return Builder
     */
    public function scopeStartedBetween(Builder $query, Carbon $from, Carbon $to): Builder
    {
        // if start is optional then model has started if; no start date, or start date is between range
        if ($this->start_optional) {
            return $query->where(function (Builder $query) use ($from, $to) {
                $query->doesntHaveStart()
                    ->orWhereBetween(
                        $this->qualifyColumn(
                            $this->getStartDateName()
                        ),
                        [$from->startOfDay(), $to->endOfDay()]
                    );
            });
        }

        // if start is NOT optional, then we just compare the datetime
        return $query->hasStart()
            ->whereBetween($this->qualifyColumn($this->getStartDateName()), [$from->startOfDay(), $to->endOfDay()]);
    }

    /**
     * Scope the query to only models where their active date started on or after the given date.
     *
     * @param  Builder  $query
     * @param  Carbon  $date
     * @return Builder
     */
    public function scopeStartedOnOrAfter(Builder $query, Carbon $date): Builder
    {
        // if start is optional then model has started if; no start date, or start date is >= $date
        if ($this->start_optional) {
            return $query->where(function (Builder $query) use ($date) {
                $query->doesntHaveStart()
                    ->orWhere($this->qualifyColumn($this->getStartDateName()), '>=', $date);
            });
        }

        // if start is NOT optional, then we just compare the datetime
        return $query->hasStart()
            ->where($this->qualifyColumn($this->getStartDateName()), '>=', $date);
    }

    /* Mutators */

    /**
     * Generic way of getting the end date value.
     *
     * @param  Carbon|string|null  $value
     * @return Carbon|null
     */
    public function getEndDateAttribute($value): ?Carbon
    {
        $attrName = $this->getEndDateName();

        // if the attribute exists and has a value, cast to Carbon and return it
        if (! is_null($value)) {
            return Carbon::parse($value);
        }

        // if the attribute is `null`, but the attribute name is the same as the mutator, return `null`
        // otherwise we get into an infinite loop of this mutator calling itself
        if (Str::studly($attrName) == Str::studly('end_date')) {
            return null;
        }

        // get the attribute value from its custom attribute
        return empty($this->getAttribute($attrName)) ? null : Carbon::parse($this->getAttribute($attrName));
    }

    /**
     * Generic way of setting the end date value.
     *
     * @param  Carbon|string|null  $value
     */
    public function setEndDateAttribute($value): void
    {
        $attrName = $this->getEndDateName();

        $this->attributes[$attrName] = is_null($value) ? null : Carbon::parse($value);
    }

    /**
     * Generic way of getting the start date value.
     *
     * @param  Carbon|string|null  $value
     * @return Carbon
     */
    public function getStartDateAttribute($value): ?Carbon
    {
        $attrName = $this->getStartDateName();

        // if the attribute exists and has a value, cast to Carbon and return it
        if (! is_null($value)) {
            return Carbon::parse($value);
        }

        // if the attribute is `null`, but the attribute name is the same as the mutator, return `null`
        // otherwise we get into an infinite loop of this mutator calling itself
        if (Str::studly($attrName) == Str::studly('start_date')) {
            return null;
        }

        // get the attribute value from its custom attribute
        return $this->getAttribute($attrName) ?? null;
    }

    /**
     * Generic way of setting the start date value.
     *
     * @param  Carbon|string|null  $value
     */
    public function setStartDateAttribute($value): void
    {
        $attrName = $this->getStartDateName();

        $this->attributes[$attrName] = is_null($value) ? null : Carbon::parse($value);
    }

    /**
     * Whether the model's date range has ended or not.
     *
     * _Note: the date range may not have started.
     * See {@link HasDateRange::getIsActiveAttribute} to see if the model is active._
     *
     * @return bool
     *
     * @see {@link HasDateRange::getHasStartedAttribute()} to see if the model has started.
     * @see {@link HasDateRange::getIsActiveAttribute()} to see if the model is active.
     */
    public function getHasEndedAttribute(): bool
    {
        return $this->endedBy(Carbon::now());
    }

    /**
     * Whether the model's date range has started or not.
     *
     * _Note: the date range may have also ended.
     * See {@link HasDateRange::getIsActiveAttribute} to see if the model is active._
     *
     * @return bool
     *
     * @see {@link HasDateRange::getHasEndedAttribute()} to see if the model has ended.
     * @see {@link HasDateRange::getIsActiveAttribute()} to see if the model is active.
     */
    public function getHasStartedAttribute(): bool
    {
        return $this->startedBy(Carbon::now());
    }

    /**
     * Whether the model's dates are currently active or not.
     *
     * @return bool
     *
     * @see {@link HasDateRange::getHasStartedAttribute()} to see if the model has started.
     * @see {@link HasDateRange::getHasEndedAttribute()} to see if the model has ended.
     */
    public function getIsActiveAttribute(): bool
    {
        return $this->activeOn(Carbon::now());
    }

    /**
     * Whether an empty value for the start date means that the model has started.
     *
     * This is useful for models that may be always active, unless they have a date range to specify their active date
     * range.
     * An example of this would be a shop discount code. It could be a non-expiring code, or it may be one that needs
     * to by used by a specific date.
     *
     * @param $value
     * @return bool
     */
    public function getStartOptionalAttribute($value): bool
    {
        if (! is_null($value)) {
            return (bool) $value;
        }

        // if the class has its own property defined, use it
        if (property_exists($this, 'startOptional')) {
            return (bool) $this->startOptional;
        }

        return false;
    }

    /* Methods */

    /**
     * Activate the model, by removing the end date and, if necessary setting a start date.
     *
     * @param  Carbon|null  $date
     * @return bool
     */
    public function activate(?Carbon $date): bool
    {
        if (isset($date)) {
            // we have an explicit start date - set it, even if the model already has a start date
            $this->start_date = $date;
        } elseif (is_null($this->start_date) && ! $this->start_optional) {
            // the model has no current start date, and start date is not optional
            $this->start_date = Carbon::now();
        }

        return $this->update([
            $this->getEndDateName() => null,
        ]);
    }

    /**
     * Return whether the model's dates were active on the given date.
     *
     * @param  Carbon  $date
     * @return bool
     */
    public function activeOn(Carbon $date): bool
    {
        return $this->startedBy($date) && ! $this->endedBy($date);
    }

    /**
     * De-activate the model by setting its end date.
     *
     * @param  Carbon|null  $date Optional. The end date. If not specified, the current datetime will be used.
     * @return bool
     */
    public function deactivate(?Carbon $date = null): bool
    {
        return $this->update([$this->getEndDateName() => $date ?? Carbon::now()]);
    }

    /**
     * Return whether the model's date range had ended by the given date.
     *
     * @param  Carbon  $date
     * @return bool
     */
    public function endedBy(Carbon $date): bool
    {
        if (is_null($this->end_date)) {
            // no end date
            return false;
        }

        return $this->end_date->lessThanOrEqualTo($date);
    }

    /**
     * Returns the field name for the end date.
     *
     * @return string
     */
    public function getEndDateName(): string
    {
        return $this->endDateName ?? 'end_date';
    }

    /**
     * Returns the field name for the start date.
     *
     * @return string
     */
    public function getStartDateName(): string
    {
        return $this->startDateName ?? 'start_date';
    }

    /**
     * Whether the model's date range had started by the given date.
     *
     * @param  Carbon  $date
     * @return bool
     */
    public function startedBy(Carbon $date): bool
    {
        if (is_null($this->start_date)) {
            // no start date
            return $this->start_optional;
        }

        return $this->start_date->lessThanOrEqualTo($date);
    }
}
