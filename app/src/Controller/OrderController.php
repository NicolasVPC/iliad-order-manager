<?php

namespace App\Controller;

use App\Entity\Order;
use App\Entity\OrderProduct;
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
    
        // Remove related OrderProduct entities
        foreach ($order->getOrderProductId() as $orderProduct) {
            $order->removeOrderProductId($orderProduct);
            $entityManager->remove($orderProduct);
        }
    
        // Remove the order
        $entityManager->remove($order);
        $entityManager->flush();
    
        return $this->json([
            'message' => "Order with ID {$data['order_id']} deleted successfully!",
        ], JsonResponse::HTTP_OK);
    }    

    public function createOrderTest(Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (!isset($data['name']) || !isset($data['description']) || !isset($data['date']) || !isset($data['products'])) {
            return $this->json([
                'error' => 'Name, description, date, and products are required.',
            ], JsonResponse::HTTP_BAD_REQUEST);
        }

        $order = new Order();
        $order->setName($data['name']);
        $order->setDescription($data['description']);
        $order->setDate(new DateTime($data['date']));

        foreach ($data['products'] as $productData) {
            if (isset($productData['id']) && isset($productData['quantity'])) {
                $product = $entityManager->getRepository(Product::class)->find($productData['id']);
                if ($product) {
                    if ($product->getStock() >= $productData['quantity']) {
                        $orderProduct = new OrderProduct();
                        $orderProduct->setOrderId($order);
                        $orderProduct->setProductId($product);
                        $orderProduct->setQuantity($productData['quantity']);

                        $order->addOrderProductId($orderProduct);

                        $entityManager->persist($orderProduct);
                    } else {
                        return $this->json([
                            'error' => "Product with ID {$productData['id']} has insufficient stock (requested: {$productData['quantity']}).",
                        ], JsonResponse::HTTP_BAD_REQUEST);
                    }
                } else {
                    return $this->json([
                        'error' => "Product with ID {$productData['id']} not found.",
                    ], JsonResponse::HTTP_BAD_REQUEST);
                }
            } else {
                return $this->json([
                    'error' => 'Each product must have an ID and a quantity.',
                ], JsonResponse::HTTP_BAD_REQUEST);
            }
        }

        $entityManager->persist($order);
        $entityManager->flush();

        return $this->json([
            'message' => 'Order created successfully.',
            'order_id' => $order->getId(),
        ], JsonResponse::HTTP_CREATED);
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

        // Update order details
        if (isset($data['name'])) {
            $order->setName($data['name']);
        }
        if (isset($data['description'])) {
            $order->setDescription($data['description']);
        }
        if (isset($data['date'])) {
            $order->setDate(new DateTime($data['date']));
        }

        // Update products (id and quantity)
        if (isset($data['products'])) {
            // Remove existing products (OrderProduct)
            foreach ($order->getOrderProductId() as $orderProduct) {
                $order->removeOrderProductId($orderProduct); // Assuming a removeOrderProduct method exists
                $entityManager->remove($orderProduct); // Delete the OrderProduct entity
            }

            // Add updated products with quantity
            foreach ($data['products'] as $productData) {
                if (isset($productData['id']) && isset($productData['quantity'])) {
                    $product = $entityManager->getRepository(Product::class)->find($productData['id']);
                    if ($product) {
                        if ($product->getStock() >= $productData['quantity']) {
                            $orderProduct = new OrderProduct();
                            $orderProduct->setOrderId($order);
                            $orderProduct->setProductId($product);
                            $orderProduct->setQuantity($productData['quantity']);
                            $order->addOrderProductId($orderProduct); // Assuming an addOrderProduct method exists
                            $entityManager->persist($orderProduct);
                        } else {
                            return $this->json([
                                'error' => "Product with ID {$productData['id']} has insufficient stock (requested: {$productData['quantity']}).",
                            ], JsonResponse::HTTP_BAD_REQUEST);
                        }
                    } else {
                        return $this->json([
                            'error' => "Product with ID {$productData['id']} not found.",
                        ], JsonResponse::HTTP_BAD_REQUEST);
                    }
                } else {
                    return $this->json([
                        'error' => 'Each product must have an ID and a quantity.',
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
