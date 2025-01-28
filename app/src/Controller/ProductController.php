<?php

namespace App\Controller;

use App\Entity\Product;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

final class ProductController extends AbstractController
{
    public function index(): JsonResponse
    {
        return $this->json([
            'message' => 'Welcome to your new controller!',
            'path' => 'src/Controller/ProductController.php',
        ]);
    }

    public function createProduct(Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (!isset($data['name'], $data['price'], $data['stock'])) {
            return $this->json([
                'error' => 'Invalid input data.',
            ], JsonResponse::HTTP_BAD_REQUEST);
        }

        $product = new Product();
        $product->setName($data['name']);
        $product->setPrice($data['price']);
        $product->setStock($data['stock']);

        $entityManager->persist($product);
        $entityManager->flush();

        return $this->json([
            'message' => 'Product created successfully!',
            'product_id' => $product->getId(),
        ]);
    }

    public function getProduct(Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        $id = $request->query->get('id');

        if (!$id) {
            return $this->json([
                'error' => 'Product ID is required.',
            ], JsonResponse::HTTP_BAD_REQUEST);
        }

        $product = $entityManager->getRepository(Product::class)->find($id);

        if (!$product) {
            return $this->json([
                'error' => 'Product not found.',
            ], JsonResponse::HTTP_NOT_FOUND);
        }

        return $this->json([
            'id' => $product->getId(),
            'name' => $product->getName(),
            'price' => $product->getPrice(),
            'stock' => $product->getStock(),
        ]);
    }
}
