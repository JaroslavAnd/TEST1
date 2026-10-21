<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ProductController extends AbstractController
{
    private const PRODUCTS = [
        [
            'id' => 1,
            'name' => 'Product 1',
            'description' => 'Description 1',
            'price' => 100,
        ],
        [
            'id' => 2,
            'name' => 'Product 2',
            'description' => 'Description 2',
            'price' => 200,
        ],
        [
            'id' => 3,
            'name' => 'Product 3',
            'description' => 'Description 3',
            'price' => 300,
        ],
    ];

    /**
     * @return JsonResponse
     */
    #[Route('/products', name: 'get_products', methods: [Request::METHOD_GET])]
    public function getProducts(): JsonResponse
    {
        return new JsonResponse(['data' => self::PRODUCTS], Response::HTTP_OK);
    }

    /**
     * @param string $id
     * @return JsonResponse
     */
    #[Route('/products/{id}', name: 'get_product_item', methods: [Request::METHOD_GET])]
    public function getProductItem(string $id): JsonResponse
    {
        $product = $this->getProductItemById(self::PRODUCTS, $id);

        if (!$product) {
            return new JsonResponse(['data' => ['error' => 'Not found product by id ' . $id]], Response::HTTP_NOT_FOUND);
        }

        return new JsonResponse(['data' => $product], Response::HTTP_OK);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    #[Route('/products', name: 'post_products', methods: [Request::METHOD_POST])]
    public function createProduct(Request $request): JsonResponse
    {
        $requestData = json_decode($request->getContent(), true) ?? [];

        $productId = random_int(1, 100);

        $newProductData = [
            'id' => $productId,
            'name' => $requestData['name'] ?? '',
            'description' => $requestData['description'] ?? '',
            'price' => $requestData['price'] ?? 0,
        ];

        // TODO insert to db

        return new JsonResponse(['data' => $newProductData], Response::HTTP_CREATED);
    }

    /**
     * @param Request $request
     * @param string $id
     * @return JsonResponse
     */
    #[Route('/products/{id}', name: 'put_products', methods: [Request::METHOD_PUT, Request::METHOD_PATCH])]
    public function updateProduct(Request $request, string $id): JsonResponse
    {
        $product = $this->getProductItemById(self::PRODUCTS, $id);

        if (!$product) {
            return new JsonResponse(['data' => ['error' => 'Not found product by id ' . $id]], Response::HTTP_NOT_FOUND);
        }

        $requestData = json_decode($request->getContent(), true) ?? [];

        $updatedProductData = [
            'id' => (int)$id,
            'name' => $requestData['name'] ?? $product['name'],
            'description' => $requestData['description'] ?? $product['description'],
            'price' => $requestData['price'] ?? $product['price'],
        ];

        // TODO update in db

        return new JsonResponse(['data' => $updatedProductData], Response::HTTP_OK);
    }

    /**
     * @param string $id
     * @return JsonResponse
     */
    #[Route('/products/{id}', name: 'delete_products', methods: [Request::METHOD_DELETE])]
    public function deleteProduct(string $id): JsonResponse
    {
        $product = $this->getProductItemById(self::PRODUCTS, $id);

        if (!$product) {
            return new JsonResponse(['data' => ['error' => 'Not found product by id ' . $id]], Response::HTTP_NOT_FOUND);
        }

        // TODO delete from db

        return new JsonResponse(['data' => ['message' => 'Product with id ' . $id . ' has been deleted']], Response::HTTP_OK);
    }

    /**
     * @param array $products
     * @param string $id
     * @return array|null
     */
    private function getProductItemById(array $products, string $id): ?array
    {
        foreach ($products as $product) {
            if ((string)$product['id'] === $id) {
                return $product;
            }
        }

        return null;
    }
}
