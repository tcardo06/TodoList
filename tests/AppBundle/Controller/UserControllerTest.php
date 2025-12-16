<?php

namespace Tests\AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use AppBundle\Entity\User;

class UserControllerTest extends WebTestCase
{
    /**
     * Helper pour connecter un utilisateur dans le firewall "main"
     */
    private function loginAsUser($client, User $user)
    {
        $session = $client->getContainer()->get('session');

        $token = new UsernamePasswordToken($user, null, 'main', $user->getRoles());
        $session->set('_security_main', serialize($token));
        $session->save();

        $client->getCookieJar()->set(
            new \Symfony\Component\BrowserKit\Cookie(
                $session->getName(),
                $session->getId()
            )
        );
    }

    /**
     * L'admin doit pouvoir voir la liste des utilisateurs (/users)
     */
    public function testAdminCanSeeUserList()
    {
        $client    = static::createClient();
        $container = self::$kernel->getContainer();
        $em        = $container->get('doctrine')->getManager();

        // On récupère l'admin créé par les fixtures
        $admin = $em->getRepository(User::class)->findOneBy(['username' => 'admin']);
        $this->assertNotNull($admin, 'L’utilisateur admin doit exister en base (fixtures).');

        $this->loginAsUser($client, $admin);

        $client->request('GET', '/users');

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        // Dans le twig user/list.html.twig, il y a normalement ce titre
        $this->assertContains(
            'Liste des utilisateurs',
            $client->getResponse()->getContent()
        );
    }

    /**
     * L'admin doit pouvoir créer un nouvel utilisateur via /users/create
     */
    public function testAdminCanCreateUser()
    {
        $client    = static::createClient();
        $container = self::$kernel->getContainer();
        $em        = $container->get('doctrine')->getManager();

        // Connexion en tant qu'admin
        $admin = $em->getRepository(User::class)->findOneBy(['username' => 'admin']);
        $this->assertNotNull($admin, 'L’utilisateur admin doit exister en base (fixtures).');

        $this->loginAsUser($client, $admin);

        // Aller sur la page de création
        $crawler = $client->request('GET', '/users/create');
        $this->assertEquals(200, $client->getResponse()->getStatusCode());

        // On génère un username/email unique pour éviter les collisions
        $uniqueSuffix = uniqid();
        $username     = 'new_user_' . $uniqueSuffix;
        $email        = 'new_user_' . $uniqueSuffix . '@example.com';

        // Soumission du formulaire (bouton "Ajouter" dans create.html.twig)
        $form = $crawler->selectButton('Ajouter')->form([
            'user[username]'          => $username,
            'user[password][first]'   => 'password',
            'user[password][second]'  => 'password',
            'user[email]'             => $email,
        ]);

        $client->submit($form);
        $client->followRedirect(); // redirection vers /users

        $this->assertEquals(200, $client->getResponse()->getStatusCode());

        // Vérifier que l’utilisateur a bien été créé en base
        $createdUser = $em->getRepository(User::class)->findOneBy(['username' => $username]);

        $this->assertNotNull($createdUser, "L'utilisateur doit être créé en base de données.");
        $this->assertEquals($email, $createdUser->getEmail());
    }
}
