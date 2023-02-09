<?php

namespace GreenImp\DateRange;

use Carbon\Carbon;
use GreenImp\DateRange\Options\DateRangeOptions;
use GreenImp\DateRange\Options\DateRangesOptions;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

trait InteractsWithDateRanges
{
    protected DateRangesOptions $dateRangesOptions;

    /**
     * Boot the trait.
     *
     * @return void
     */
    public static function bootInteractsWithDateRanges()
    {
        static::deleting(function (self $model) {
            if (in_array(SoftDeletes::class, class_uses_recursive($model))) {
                if (! $model->forceDeleting) {
                    return;
                }
            }

            $model->{config('date-range.relationship.name_on_parent')}()->cursor()->each->delete();
        });

        static::resolveRelationUsing(config('date-range.relationship.name_on_parent'), function (self $model) {
            $options = $this->getDateRangesOptions();

            if ($options->polymorphic) {
                return $model->morphMany($options->dateRangeModel, $options->foreignKeyName);
            } else {
                return $model->hasMany($options->dateRangeModel, $options->foreignKeyName.'_id');
            }
        });
    }

    /**
     * Initialise the trait.
     *
     * @return void
     */
    protected function initializeInteractsWithDateRanges(): void
    {
        $this->dateRangesOptions = $this->getDateRangesOptions();
    }

    public function scopeIsActive(Builder $query): Builder
    {
        return $query->whereHas(
            config('date-range.relationship.name_on_parent'),
            fn (Builder $query) => $query->isActive()
        );
    }

    public function scopeIsActiveOn(Builder $query, Carbon $date): Builder
    {
        return $query->whereHas(
            config('date-range.relationship.name_on_parent'),
            fn (Builder $query) => $query->isActiveOn($date)
        );
    }

    public function scopeIsInactive(Builder $query): Builder
    {
        return $query->whereDoesntHave(
            config('date-range.relationship.name_on_parent'),
            fn (Builder $query) => $query->isActive()
        );
    }

    public function scopeIsInactiveOn(Builder $query, Carbon $date): Builder
    {
        return $query->whereDoesntHave(
            config('date-range.relationship.name_on_parent'),
            fn (Builder $query) => $query->isActiveOn($date)
        );
    }

    public function scopeOrderByDateRange(Builder $query, string $direction = 'asc'): Builder
    {
        /** @var DateRangeOptions $dateRangeConfig */
        $dateRangeOptions = (new ($this->getDateRangesOptions()->dateRangeModel))->getDateRangeOptions();

        if ($direction === 'asc') {
            return $query
                ->orderBy(
                    $this->getDatesOrderQuery($direction)
                        ->select($dateRangeOptions->startAtField)
                )
                ->orderBy(
                    $this->getDatesOrderQuery($direction)
                        ->select($dateRangeOptions->endAtField)
                );
        }

        return $query
            ->orderBy(
                $this->getDatesOrderQuery($direction)
                    ->select($dateRangeOptions->endAtField)
            )
            ->orderBy(
                $this->getDatesOrderQuery($direction)
                    ->select($dateRangeOptions->startAtField)
            );
    }

    protected function getDatesOrderQuery($direction = 'asc'): Builder
    {
        $options = $this->getDateRangesOptions();
        /** @var Model $dateModel */
        $dateModel = new ($options->dateRangeModel);

        return $dateModel
            ->newQuery()
            ->whereColumn(
                $dateModel->qualifyColumn($options->foreignKeyName.'_id'),
                $this->getQualifiedKeyName()
            )
            ->orderBy($dateModel->qualifyColumn($options->foreignKeyName.'_id'), $direction)
            ->take(1);
    }

    public function isActive(): bool
    {
        return $this->{config('date-range.relationship.name_on_parent')}()
            ->isActive()
            ->exists();
    }

    public function isActiveOn(Carbon $date): bool
    {
        return $this->{config('date-range.relationship.name_on_parent')}()
            ->isActiveOn($date)
            ->exists();
    }

    public function activate(?Carbon $date): bool
    {
        $date = $date ?? Carbon::now();

        if ($this->isActiveOn($date)) {
            return true;
        }

        /** @var DateRangeOptions $dateRangeConfig */
        $dateRangeOptions = (new ($this->getDateRangesOptions()->dateRangeModel))->getDateRangeOptions();

        $this->{config('date-range.relationship.name_on_parent')}()->create([
            $dateRangeOptions->startAtField => $date,
            $dateRangeOptions->endAtField => null,
        ]);

        return true;
    }

    public function deactivate(?Carbon $date): bool
    {
        $date = $date ?? Carbon::now();

        $this->{config('date-range.relationship.name_on_parent')}()
            ->activeOn($date)
            ->cursor()
            ->each
            ->deactivate($date);

        return true;
    }

    /**
     * Get the options for the date ranges.
     *
     * @return DateRangesOptions
     */
    abstract public function getDateRangesOptions(): DateRangesOptions;
}
