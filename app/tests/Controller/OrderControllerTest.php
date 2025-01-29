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
    
        $timestamp = 1738095557;
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
                'products' => [1,2,3,4],
            ])
        );
    
        self::assertResponseIsSuccessful();
        self::assertSame(200, $client->getResponse()->getStatusCode());
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
                'products' => [1, 2],
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
}
