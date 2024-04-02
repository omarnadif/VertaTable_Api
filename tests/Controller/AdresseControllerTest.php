<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class AdresseControllerTest extends WebTestCase
{
public function testIndex()
{
$client = static::createClient();
$client->request('GET', '/api/adresse');

$this->assertEquals(200, $client->getResponse()->getStatusCode());
}
}
