<?php

namespace App\MessageHandler;

use App\Entity\Order;
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

        foreach ($data['products'] as $productId) {
            $product = $this->entityManager->getRepository(Product::class)->find($productId);
            if ($product) {
                $order->addIdProduct($product);
            } else {
                echo "Product with ID {$productId} not found.\n";
                return;
            }
        }

        $this->entityManager->persist($order);
        $this->entityManager->flush();

        echo "Order ID " . $order->getId() . " created successfully.\n";
    }
}
