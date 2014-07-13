<?php

namespace Hvz\GameBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ViewTestControllerTest extends WebTestCase
{
    public function testBootstrapbase()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/test/bootstrap/base');
    }

}
