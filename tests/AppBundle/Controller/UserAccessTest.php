<?php

namespace Tests\AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use AppBundle\Entity\User;

class UserAccessTest extends WebTestCase
{
    private function loginAs($client, User $user)
    {
        $session = $client->getContainer()->get('session');

        $token = new UsernamePasswordToken($user, null, 'main', $user->getRoles());
        $session->set('_security_main', serialize($token));
        $session->save();

        $client->getCookieJar()->set(new \Symfony\Component\BrowserKit\Cookie(
            $session->getName(), $session->getId()
        ));
    }

    public function testUserCannotAccessUserManagement()
    {
        $client = static::createClient();
        $em = self::$kernel->getContainer()->get('doctrine')->getManager();

        $user = $em->getRepository(User::class)->findOneBy(['username' => 'user']);
        $this->loginAs($client, $user);

        $client->request('GET', '/users');

        $this->assertEquals(403, $client->getResponse()->getStatusCode());
    }

    public function testAdminCanAccessUserManagement()
    {
        $client = static::createClient();
        $em = self::$kernel->getContainer()->get('doctrine')->getManager();

        $admin = $em->getRepository(User::class)->findOneBy(['username' => 'admin']);
        $this->loginAs($client, $admin);

        $client->request('GET', '/users');

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
    }
}
