<?php

namespace App\Controller;

use App\Entity\Order;
use App\Entity\OrderProduct;
use App\Entity\Product;
use App\Message\CreateOrderMessage;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
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
    
    public function createOrder(Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (!isset($data['name'], $data['description'], $data['date'], $data['products'])) {
            return $this->json(['error' => 'Name, description, date, and products are required.'], JsonResponse::HTTP_BAD_REQUEST);
        }

        $entityManager->beginTransaction();
        try {
            $order = new Order();
            $order->setName($data['name']);
            $order->setDescription($data['description']);
            $order->setDate(new DateTime($data['date']));
            
            foreach ($data['products'] as $productData) {
                if (!isset($productData['id'], $productData['quantity'])) {
                    throw new \Exception('Each product must have an ID and a quantity.');
                }

                $product = $entityManager->getRepository(Product::class)->find($productData['id']);
                if (!$product || $product->getStock() < $productData['quantity']) {
                    throw new \Exception("Product with ID {$productData['id']} has insufficient stock or does not exist.");
                }
                
                $product->setStock($product->getStock() - $productData['quantity']);
                $entityManager->persist($product);
                
                $orderProduct = new OrderProduct();
                $orderProduct->setOrderId($order);
                $orderProduct->setProductId($product);
                $orderProduct->setQuantity($productData['quantity']);
                
                $order->addOrderProductId($orderProduct);
                $entityManager->persist($orderProduct);
            }
            
            $entityManager->persist($order);
            $entityManager->flush();
            $entityManager->commit();
            
            return $this->json(['message' => 'Order created successfully.', 'order_id' => $order->getId()], JsonResponse::HTTP_CREATED);
        } catch (\Exception $e) {
            $entityManager->rollback();
            return $this->json(['error' => $e->getMessage()], JsonResponse::HTTP_BAD_REQUEST);
        }
    }

    public function deleteOrder(Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
    
        if (!isset($data['order_id'])) {
            return $this->json(['error' => 'Order ID is required.'], JsonResponse::HTTP_BAD_REQUEST);
        }
    
        $entityManager->beginTransaction();
        try {
            $order = $entityManager->getRepository(Order::class)->find($data['order_id']);
    
            if (!$order) {
                throw new \Exception("Order with ID {$data['order_id']} not found.");
            }
    
            foreach ($order->getOrderProductId() as $orderProduct) {
                $product = $orderProduct->getProductId();
                $product->setStock($product->getStock() + $orderProduct->getQuantity());
                $entityManager->persist($product);
                
                $order->removeOrderProductId($orderProduct);
                $entityManager->remove($orderProduct);
            }
    
            $entityManager->remove($order);
            $entityManager->flush();
            $entityManager->commit();
    
            return $this->json(['message' => "Order with ID {$data['order_id']} deleted successfully!"], JsonResponse::HTTP_OK);
        } catch (\Exception $e) {
            $entityManager->rollback();
            return $this->json(['error' => $e->getMessage()], JsonResponse::HTTP_BAD_REQUEST);
        }
    }

    public function updateOrder(Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (!isset($data['order_id'])) {
            return $this->json(['error' => 'Order ID is required.'], JsonResponse::HTTP_BAD_REQUEST);
        }

        $entityManager->beginTransaction();
        try {
            $order = $entityManager->getRepository(Order::class)->find($data['order_id']);
            if (!$order) {
                throw new \Exception("Order with ID {$data['order_id']} not found.");
            }

            foreach ($order->getOrderProductId() as $orderProduct) {
                $product = $orderProduct->getProductId();
                $product->setStock($product->getStock() + $orderProduct->getQuantity());
                $entityManager->persist($product);
                $entityManager->remove($orderProduct);
                $order->removeOrderProductId($orderProduct);
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

            foreach ($data['products'] as $productData) {
                $product = $entityManager->getRepository(Product::class)->find($productData['id']);
                if (!$product || $product->getStock() < $productData['quantity']) {
                    throw new \Exception("Product with ID {$productData['id']} has insufficient stock or does not exist.");
                }
                $product->setStock($product->getStock() - $productData['quantity']);
                $entityManager->persist($product);
                
                $orderProduct = new OrderProduct();
                $orderProduct->setOrderId($order);
                $orderProduct->setProductId($product);
                $orderProduct->setQuantity($productData['quantity']);
                $entityManager->persist($orderProduct);
            }

            $entityManager->persist($order);
            $entityManager->flush();
            $entityManager->commit();

            return $this->json(['message' => "Order with ID {$data['order_id']} updated successfully!"], JsonResponse::HTTP_OK);
        } catch (\Exception $e) {
            $entityManager->rollback();
            return $this->json(['error' => $e->getMessage()], JsonResponse::HTTP_BAD_REQUEST);
        }
    }

    public function getOrder(Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        $orderId = $request->query->get('id');

        if (!$orderId) {
            return $this->json(['error' => 'Order ID is required.'], JsonResponse::HTTP_BAD_REQUEST);
        }

        try {
            $order = $entityManager->getRepository(Order::class)->find($orderId);
            
            if (!$order) {
                throw new \Exception("Order with ID {$orderId} not found.");
            }

            $orderProducts = [];
            foreach ($order->getOrderProductId() as $orderProduct) {
                $orderProducts[] = [
                    'product_id' => $orderProduct->getProductId()->getId(),
                    'product_name' => $orderProduct->getProductId()->getName(),
                    'quantity' => $orderProduct->getQuantity(),
                ];
            }

            $response = [
                'order_id' => $order->getId(),
                'name' => $order->getName(),
                'description' => $order->getDescription(),
                'date' => $order->getDate()->format('Y-m-d'),
                'products' => $orderProducts,
            ];

            return $this->json($response, JsonResponse::HTTP_OK);
        } catch (\Exception $e) {
            return $this->json(['error' => $e->getMessage()], JsonResponse::HTTP_BAD_REQUEST);
        }
    }
}
