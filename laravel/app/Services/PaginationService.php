<?php

namespace App\Services;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class PaginationService
{
    public const DEFAULT_PAGE = 1;
    public const DEFAULT_ITEMS_PER_PAGE = 10;
    public const MAX_ITEMS_PER_PAGE = 100;

    /**
     * Пагінація Eloquent-запиту з підтримкою itemsPerPage, per_page, limit
     */
    public function paginate(Builder $query, Request $request): array
    {
        $page = max(1, (int)$request->query('page', self::DEFAULT_PAGE));

        $itemsPerPage = (int)(
            $request->query('itemsPerPage')
            ?? $request->query('per_page')
            ?? $request->query('limit')
            ?? self::DEFAULT_ITEMS_PER_PAGE
        );

        $itemsPerPage = max(1, min(self::MAX_ITEMS_PER_PAGE, $itemsPerPage));

        $paginator = $query->paginate($itemsPerPage, ['*'], 'page', $page);

        return [
            'data' => $paginator->items(),
            'pagination' => [
                'currentPage' => $paginator->currentPage(),
                'itemsPerPage' => $paginator->perPage(),
                'totalItems' => $paginator->total(),
                'totalPages' => $paginator->lastPage(),
                'hasNextPage' => $paginator->hasMorePages(),
                'hasPreviousPage' => $paginator->currentPage() > 1,
            ],
        ];
    }
}
