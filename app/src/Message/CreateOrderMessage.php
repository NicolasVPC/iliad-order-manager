<?php

namespace App\Message;

final class CreateOrderMessage
{
    private array $orderData;

    public function __construct(array $orderData)
    {
        $this->orderData = $orderData;
    }

    public function getOrderData(): array
    {
        return $this->orderData;
    }
}
