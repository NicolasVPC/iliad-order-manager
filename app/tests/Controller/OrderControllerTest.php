<?php

namespace App\Tests\Controller;

use DateTime;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class OrderControllerTest extends WebTestCase
{
    // public function testIndex(): void
    // {
    //     $client = static::createClient();
    //     $client->request('GET', '/order');

    //     self::assertResponseIsSuccessful();
    // }
    
    public function testCreateOrder(): void
    {
        $client = static::createClient();
    
        $timestamp = 1738174913;
        $formattedDate = (new \DateTime())->setTimestamp($timestamp)->format('Y-m-d');
    
        $client->request(
            'POST',
            '/create/order',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'name' => 'nome test',
                'description' => 'description test',
                'date' => $formattedDate,
                'products' => [
                    ['id' => 1, 'quantity' => 2],
                    ['id' => 2, 'quantity' => 3],
                    ['id' => 3, 'quantity' => 1],
                    ['id' => 4, 'quantity' => 2]
                ],
            ])
        );
    
        self::assertResponseIsSuccessful();
        self::assertSame(201, $client->getResponse()->getStatusCode());
        self::assertJson($client->getResponse()->getContent());
    }

    public function testDeleteOrder(): void
    {
        $client = static::createClient();

        $timestamp = (new \DateTime())->getTimestamp();
        $formattedDate = (new \DateTime())->setTimestamp($timestamp)->format('Y-m-d');

        $client->request(
            'POST',
            '/create/order',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'name' => 'Order to Delete',
                'description' => 'This order will be deleted in the test.',
                'date' => $formattedDate,
                'products' => [
                    ['id' => 1, 'quantity' => 2],
                    ['id' => 2, 'quantity' => 3]
                ],
            ])
        );

        self::assertResponseIsSuccessful();
        $responseContent = $client->getResponse()->getContent();
        $responseData = json_decode($responseContent, true);
        self::assertArrayHasKey('order_id', $responseData);

        $orderId = $responseData['order_id'];

        $client->request(
            'DELETE',
            '/delete/order',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'order_id' => $orderId,
            ])
        );

        self::assertResponseIsSuccessful();
        self::assertSame(200, $client->getResponse()->getStatusCode());
        self::assertJson($client->getResponse()->getContent());

        $deleteResponseContent = $client->getResponse()->getContent();
        $deleteResponseData = json_decode($deleteResponseContent, true);
        self::assertSame("Order with ID {$orderId} deleted successfully!", $deleteResponseData['message']);
    }


    public function testUpdateOrder(): void
    {
        $client = static::createClient();
    
        $timestamp = (new \DateTime())->getTimestamp();
        $formattedDate = (new \DateTime())->setTimestamp($timestamp)->format('Y-m-d');
    
        $client->request(
            'POST',
            '/create/order',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'name' => 'Order to Update',
                'description' => 'This order will be updated in the test.',
                'date' => $formattedDate,
                'products' => [
                    ['id' => 1, 'quantity' => 2],
                    ['id' => 2, 'quantity' => 3]
                ],
            ])
        );
    
        self::assertResponseIsSuccessful();
        $responseContent = $client->getResponse()->getContent();
        $responseData = json_decode($responseContent, true);
        self::assertArrayHasKey('order_id', $responseData);
    
        $orderId = $responseData['order_id'];
    
        $updatedData = [
            'order_id' => $orderId,
            'name' => 'Updated Order Name',
            'description' => 'Updated description',
            'date' => (new \DateTime())->modify('+1 day')->format('Y-m-d'),
            'products' => [
                ['id' => 3, 'quantity' => 1],
                ['id' => 4, 'quantity' => 2]
            ],
        ];
    
        $client->request(
            'PUT',
            '/update/order',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($updatedData)
        );
    
        self::assertResponseIsSuccessful();
        self::assertSame(200, $client->getResponse()->getStatusCode());
        self::assertJson($client->getResponse()->getContent());
    
        $updateResponseContent = $client->getResponse()->getContent();
        $updateResponseData = json_decode($updateResponseContent, true);
        self::assertSame("Order with ID {$orderId} updated successfully!", $updateResponseData['message']);
    }    
}
