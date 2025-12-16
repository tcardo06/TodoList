<?php

namespace Tests\AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use AppBundle\Entity\User;

class SecurityAccessTest extends WebTestCase
{
    /**
     * Simule une connexion utilisateur dans les tests
     */
    private function loginAsUser($client, User $user)
    {
        $session = $client->getContainer()->get('session');

        $token = new UsernamePasswordToken($user, null, 'main', $user->getRoles());
        $session->set('_security_main', serialize($token));
        $session->save();

        // Injecte le cookie de session dans le client
        $client->getCookieJar()->set(new \Symfony\Component\BrowserKit\Cookie(
            $session->getName(), $session->getId()
        ));
    }

    public function testHomepageRedirectsToLoginWhenNotAuthenticated()
    {
        $client = static::createClient();
        $client->request('GET', '/');

        $this->assertEquals(302, $client->getResponse()->getStatusCode());
        $this->assertContains('/login', $client->getResponse()->headers->get('Location'));
    }

    public function testUserCannotAccessUserManagement()
    {
        $client = static::createClient();

        // charge un utilisateur normal depuis la BDD (fixture)
        $user = self::$kernel->getContainer()
            ->get('doctrine')
            ->getRepository(User::class)
            ->findOneBy(['username' => 'user']);

        $this->loginAsUser($client, $user);

        $client->request('GET', '/users');

        $this->assertEquals(403, $client->getResponse()->getStatusCode());
    }

    public function testAdminCanAccessUserManagement()
    {
        $client = static::createClient();

        // utilisateur admin depuis la BDD
        $admin = self::$kernel->getContainer()
            ->get('doctrine')
            ->getRepository(User::class)
            ->findOneBy(['username' => 'admin']);

        $this->loginAsUser($client, $admin);

        $crawler = $client->request('GET', '/users');

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertContains('Liste des utilisateurs', $crawler->filter('h1')->text());
    }
}
