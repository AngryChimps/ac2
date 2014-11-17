<?php

namespace AngryChimps\ApiBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class CompanyControllerTest extends WebTestCase
{
    public function testIndexpost()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/');
    }

    public function testIndexget()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/');
    }

    public function testIndexput()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/');
    }

    public function testIndexdelete()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/');
    }

}
