<?php

namespace App\Controller;

use App\Entity\Order;
use App\Entity\Product;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

final class OrderController extends AbstractController
{
    public function index(): JsonResponse
    {
        return $this->json([
            'message' => 'Welcome to your new controller!',
            'path' => 'src/Controller/OrderController.php',
        ]);
    }

    public function createOrder(Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (!isset($data['name'], $data['date'], $data['products'])) {
            return $this->json([
                'error' => 'Invalid input data.',
            ], JsonResponse::HTTP_BAD_REQUEST);
        }
        
        if(!isset($data['description'])) {
            $data['description'] = null;
        }

        $order = new Order();
        $order->setName($data['name']);
        $order->setDescription($data['description']);
        $order->setDate(new DateTime($data['date']));
        foreach ($data['products'] as $productId) {
            $product = $entityManager->getRepository(Product::class)->find($productId);
            if ($product) {
                $order->addIdProduct($product);
            } else {
                return $this->json([
                    'error' => "Product with ID {$productId} not found.",
                ], JsonResponse::HTTP_BAD_REQUEST);
            }
        }

        $entityManager->persist($order);
        $entityManager->flush();

        return $this->json([
            'message' => 'Order created successfully!',
            'order_id' => $order->getId(),
        ]);
    }
}
