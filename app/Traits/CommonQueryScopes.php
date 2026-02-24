<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;

trait CommonQueryScopes
{
    /**
     * Scope a query to filter by date.
     *
     * @param Builder $query
     * @param string|null $date
     * @param string $column
     * @return Builder
     */
    public function scopeFilterByDate(Builder $query, ?string $date, string $column = 'date'): Builder
    {
        if (!$date) {
            return $query;
        }

        try {
            $parsedDate = Carbon::parse($date)->toDateString();
            return $query->whereDate($column, $parsedDate);
        } catch (\Exception $e) {
            return $query;
        }
    }

    /**
     * Scope a query to search by title.
     *
     * @param Builder $query
     * @param string|null $title
     * @param string $column
     * @return Builder
     */
    public function scopeSearchByTitle(Builder $query, ?string $title, string $column = 'title'): Builder
    {
        if (!$title) {
            return $query;
        }

        return $query->where($column, 'LIKE', '%' . $title . '%');
    }
}
