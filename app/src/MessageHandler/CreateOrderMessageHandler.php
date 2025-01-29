<?php

namespace App\MessageHandler;

use App\Entity\Order;
use App\Entity\OrderProduct;
use App\Entity\Product;
use App\Message\CreateOrderMessage;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final class CreateOrderMessageHandler
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function __invoke(CreateOrderMessage $message)
    {
        $data = $message->getOrderData();

        $order = new Order();
        $order->setName($data['name']);
        $order->setDescription($data['description']);
        $order->setDate(new \DateTime($data['date']));

        foreach ($data['products'] as $product_el) {
            $product = $this->entityManager->getRepository(Product::class)->find($product_el['id']);
            if ($product) {
                if ($product->getStock() >= $product_el['quantity']) {
                    $orderProduct = new OrderProduct();
                    $orderProduct->setOrderId($order);
                    $orderProduct->setProductId($product);
                    $orderProduct->setQuantity($product_el['quantity']);
                    $order->addOrderProductId($orderProduct);
                    
                    $this->entityManager->persist($orderProduct);
                }
                else {
                    echo "Product with ID {$product_el['id']} has not sufficient stock (stock requested: {$product_el['quantity']}).\n";
                    return;
                }
            } else {
                echo "Product with ID {$product_el['id']} not found.\n";
                return;
            }
        }
        
        $this->entityManager->persist($order);
        $this->entityManager->flush();

        echo "Order ID " . $order->getId() . " created successfully.\n";
    }
}
