<?php

namespace App\Controller;

use App\Entity\Order;
use App\Entity\Product;
use App\Message\CreateOrderMessage;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Messenger\MessageBusInterface;

final class OrderController extends AbstractController
{
    private MessageBusInterface $bus;

    public function __construct(MessageBusInterface $bus)
    {
        $this->bus = $bus;
    }
    
    public function index(): JsonResponse
    {
        return $this->json([
            'message' => 'Welcome to your new controller!',
            'path' => 'src/Controller/OrderController.php',
        ]);
    }

    public function createOrder(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (!isset($data['name'], $data['date'], $data['products'])) {
            return new JsonResponse(['error' => 'Invalid input data.'], JsonResponse::HTTP_BAD_REQUEST);
        }

        if (!isset($data['description'])) {
            $data['description'] = null;
        }

        $this->bus->dispatch(new CreateOrderMessage($data));

        return new JsonResponse(['message' => 'Order request received, processing in queue.']);
    }

    public function deleteOrder(Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (!isset($data['order_id'])) {
            return $this->json([
                'error' => 'Order ID is required.',
            ], JsonResponse::HTTP_BAD_REQUEST);
        }

        $order = $entityManager->getRepository(Order::class)->find($data['order_id']);

        if (!$order) {
            return $this->json([
                'error' => "Order with ID {$data['order_id']} not found.",
            ], JsonResponse::HTTP_NOT_FOUND);
        }

        $entityManager->remove($order);
        $entityManager->flush();

        return $this->json([
            'message' => "Order with ID {$data['order_id']} deleted successfully!",
        ], JsonResponse::HTTP_OK);
    }

    public function updateOrder(Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (!isset($data['order_id'])) {
            return $this->json([
                'error' => 'Order ID is required.',
            ], JsonResponse::HTTP_BAD_REQUEST);
        }

        $order = $entityManager->getRepository(Order::class)->find($data['order_id']);

        if (!$order) {
            return $this->json([
                'error' => "Order with ID {$data['order_id']} not found.",
            ], JsonResponse::HTTP_NOT_FOUND);
        }

        if (isset($data['name'])) {
            $order->setName($data['name']);
        }
        if (isset($data['description'])) {
            $order->setDescription($data['description']);
        }
        if (isset($data['date'])) {
            $order->setDate(new DateTime($data['date']));
        }

        if (isset($data['products'])) {
            foreach ($order->getIdProduct() as $product) {
                $order->removeIdProduct($product);
            }

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
        }

        $entityManager->flush();

        return $this->json([
            'message' => "Order with ID {$data['order_id']} updated successfully!",
        ]);
    }
}
