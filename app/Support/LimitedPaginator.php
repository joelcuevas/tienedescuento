<?php

namespace App\Support;

use Illuminate\Pagination\LengthAwarePaginator;

class LimitedPaginator extends LengthAwarePaginator
{
    public function __construct($items, $total, $perPage, $currentPage = null, $options = [], $limit = null)
    {
        $limitedTotal = $limit ? min($total, $limit) : $total;
        $options['path'] = $options['path'] ?? LengthAwarePaginator::resolveCurrentPath();

        parent::__construct($items, $limitedTotal, $perPage, $currentPage, $options);
    }

    public static function fromQuery($query, $perPage, $limit = null, $options = [])
    {
        $currentPage = LengthAwarePaginator::resolveCurrentPage();

        $total = $query->count();
        $limitedQuery = $query->take($limit ?? $total)->skip(($currentPage - 1) * $perPage);
        $items = $limitedQuery->take($perPage)->get();
        $options['path'] = $options['path'] ?? LengthAwarePaginator::resolveCurrentPath();

        return new static(
            $items,
            $total,
            $perPage,
            $currentPage,
            $options,
            $limit,
        );
    }
}