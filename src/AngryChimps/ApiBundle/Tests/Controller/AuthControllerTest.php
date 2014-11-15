<?php

namespace AngryChimps\ApiBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class AuthControllerTest extends WebTestCase
{
    public function testLogin()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/login');
    }

    public function testLogout()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/logout');
    }

    public function testChangepassword()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/changePassword');
    }

    public function testForgotpassword()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/forgotPassword');
    }

}
