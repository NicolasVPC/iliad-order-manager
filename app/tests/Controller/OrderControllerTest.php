<?php

namespace App\Tests\Controller;

use DateTime;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class OrderControllerTest extends WebTestCase
{
    public function testIndex(): void
    {
        $client = static::createClient();
        $client->request('GET', '/order');

        self::assertResponseIsSuccessful();
    }
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
                'date' => $formattedDate, // Passa la data formattata
            ])
        );
        
        self::assertResponseIsSuccessful();
        self::assertSame(200, $client->getResponse()->getStatusCode());
        self::assertJson($client->getResponse()->getContent());
    }
}
