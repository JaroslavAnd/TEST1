<?php

namespace App\Service;

use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Symfony\Component\HttpFoundation\Request;

class PaginationService
{
    public const DEFAULT_PAGE = 1;
    public const DEFAULT_ITEMS_PER_PAGE = 10;
    public const MAX_ITEMS_PER_PAGE = 100;

    /**
     * @param QueryBuilder $qb
     * @param Request $request
     * @param callable|null $transformer Optional callback to transform each item
     * @return array
     */
    public function paginate(QueryBuilder $qb, Request $request, ?callable $transformer = null): array
    {
        $page = max(1, (int)$request->query->get('page', self::DEFAULT_PAGE));
        
        // Підтримка параметрів itemsPerPage, limit, per_page
        $itemsPerPage = (int)($request->query->get('itemsPerPage') 
            ?? $request->query->get('limit') 
            ?? $request->query->get('per_page') 
            ?? self::DEFAULT_ITEMS_PER_PAGE);

        $itemsPerPage = max(1, min(self::MAX_ITEMS_PER_PAGE, $itemsPerPage));

        $firstResult = ($page - 1) * $itemsPerPage;

        $qb->setFirstResult($firstResult)
           ->setMaxResults($itemsPerPage);

        $paginator = new Paginator($qb, fetchJoinCollection: true);
        $totalItems = count($paginator);
        $totalPages = (int)ceil($totalItems / $itemsPerPage);

        $items = [];
        foreach ($paginator as $item) {
            $items[] = $transformer ? $transformer($item) : (method_exists($item, 'toArray') ? $item->toArray() : $item);
        }

        return [
            'data' => $items,
            'pagination' => [
                'currentPage' => $page,
                'itemsPerPage' => $itemsPerPage,
                'totalItems' => $totalItems,
                'totalPages' => $totalPages,
                'hasNextPage' => $page < $totalPages,
                'hasPreviousPage' => $page > 1,
            ],
        ];
    }
}
