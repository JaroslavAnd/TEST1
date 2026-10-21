<?php

namespace App\Http\Controllers;

use Exception;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ProductController extends Controller
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
     * @return mixed
     */
    public function getProducts(): mixed
    {
        return response()->json(['data' => self::PRODUCTS], Response::HTTP_OK);
    }

    /**
     * @param string $id
     * @return mixed
     */
    public function getProductItem(string $id): mixed
    {
        $product = $this->getProductItemById(self::PRODUCTS, $id);

        if (!$product) {
            return response()->json(['data' => ['error' => 'Not found product by id ' . $id]], Response::HTTP_NOT_FOUND);
        }

        return response()->json(['data' => $product], Response::HTTP_OK);
    }

    /**
     * @param Request $request
     * @return mixed
     * @throws Exception
     */
    public function createProduct(Request $request): mixed
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

        return response()->json(['data' => $newProductData], Response::HTTP_CREATED);
    }

    /**
     * @param Request $request
     * @param string $id
     * @return mixed
     */
    public function updateProduct(Request $request, string $id): mixed
    {
        $product = $this->getProductItemById(self::PRODUCTS, $id);

        if (!$product) {
            return response()->json(['data' => ['error' => 'Not found product by id ' . $id]], Response::HTTP_NOT_FOUND);
        }

        $requestData = json_decode($request->getContent(), true) ?? [];

        $updatedProductData = [
            'id' => (int)$id,
            'name' => $requestData['name'] ?? $product['name'],
            'description' => $requestData['description'] ?? $product['description'],
            'price' => $requestData['price'] ?? $product['price'],
        ];

        // TODO update in db

        return response()->json(['data' => $updatedProductData], Response::HTTP_OK);
    }

    /**
     * @param string $id
     * @return mixed
     */
    public function deleteProduct(string $id): mixed
    {
        $product = $this->getProductItemById(self::PRODUCTS, $id);

        if (!$product) {
            return response()->json(['data' => ['error' => 'Not found product by id ' . $id]], Response::HTTP_NOT_FOUND);
        }

        // TODO delete from db

        return response()->json(['data' => ['message' => 'Product with id ' . $id . ' has been deleted']], Response::HTTP_OK);
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
