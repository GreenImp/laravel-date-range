<?php

namespace GreenImp\DateRange;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;;

trait InteractsWithDateRanges
{
    public static function bootInteractsWithMedia()
    {
        static::deleting(function (self $model) {
            if (in_array(SoftDeletes::class, class_uses_recursive($model))) {
                if (! $model->forceDeleting) {
                    return;
                }
            }

            $model->dates()->cursor()->each->delete();
        });
    }

    public function dates(): MorphMany
    {
        return $this->morphMany(config('date-ranges.models.date_range'), 'model');
    }

    public function scopeIsActive(Builder $query): Builder
    {
        return $query->whereHas('dates', fn (Builder $query) => $query->isActive());
    }

    public function scopeIsActiveOn(Builder $query, Carbon $date): Builder
    {
        return $query->whereHas('dates', fn (Builder $query) => $query->isActiveOn($date));
    }

    public function scopeIsInactive(Builder $query): Builder
    {
        return $query->whereDoesntHave('dates', fn (Builder $query) => $query->isActive());
    }

    public function scopeIsInactiveOn(Builder $query, Carbon $date): Builder
    {
        return $query->whereDoesntHave('dates', fn (Builder $query) => $query->isActiveOn($date));
    }

    public function scopeOrderByDateRange(Builder $query, string $direction = 'asc'): Builder
    {
        /** @var DateRangeOptions $dateRangeConfig */
        $dateRangeOptions = (new (config('date-ranges.models.date_range')))->getDateRangeOptions();

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
        /** @var Model $dateModel */
        $dateModel = new (config('date-ranges.models.date_range'));

        return $dateModel
            ->newQuery()
            ->whereColumn(
                $dateModel->qualifyColumn(config('date-ranges.column_names.model_morph_key')),
                $this->getQualifiedKeyName()
            )
            ->orderBy($dateModel->qualifyColumn(config('date-ranges.column_names.model_morph_key')), $direction)
            ->take(1);
    }

    public function isActive(): bool
    {
        return $this->dates()->isActive()->exists();
    }

    public function isActiveOn(Carbon $date): bool
    {
        return $this->dates()->isActiveOn($date)->exists();
    }

    public function activate(?Carbon $date): bool
    {
        $date = $date ?? Carbon::now();

        if ($this->isActiveOn($date)) {
            return true;
        }

        /** @var DateRangeOptions $dateRangeConfig */
        $dateRangeOptions = (new (config('date-ranges.models.date_range')))->getDateRangeOptions();

        $this->dates()->create([
            $dateRangeOptions->startAtField => $date,
            $dateRangeOptions->endAtField => null,
        ]);

        return true;
    }

    public function deactivate(?Carbon $date): bool
    {
        $date = $date ?? Carbon::now();

        $this->dates()->activeOn($date)->cursor()->each->deactivate($date);

        return true;
    }
}
