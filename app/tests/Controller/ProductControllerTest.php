<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class ProductControllerTest extends WebTestCase
{
    public function testIndex(): void
    {
        $client = static::createClient();
        $client->request('GET', '/create/product');

        self::assertResponseIsSuccessful();
    }

    public function testCreateProduct(): void
    {
        $client = static::createClient();
        $client->request(
            'POST',
            '/create/product',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'name' => 'nome_test',
                'price' => '10000',
                'stock' => '10'
            ])
        );
        
        self::assertResponseIsSuccessful();
        self::assertSame(200, $client->getResponse()->getStatusCode());
        self::assertJson($client->getResponse()->getContent());
    }
}
