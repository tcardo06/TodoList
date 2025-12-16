<?php

namespace Tests\AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use AppBundle\Entity\User;
use AppBundle\Entity\Task;

class TaskControllerTest extends WebTestCase
{
    private function loginAsUser($client, User $user)
    {
        $session = $client->getContainer()->get('session');

        $token = new UsernamePasswordToken($user, null, 'main', $user->getRoles());
        $session->set('_security_main', serialize($token));
        $session->save();

        $client->getCookieJar()->set(new \Symfony\Component\BrowserKit\Cookie(
            $session->getName(), $session->getId()
        ));
    }


    public function testUserCanCreateTaskAndIsAuthor()
    {
        $client = static::createClient();
        $container = self::$kernel->getContainer();
        $em = $container->get('doctrine')->getManager();

        $user = $em->getRepository(User::class)->findOneBy(['username' => 'user']);
        $this->loginAsUser($client, $user);

        $crawler = $client->request('GET', '/tasks/create');

        // Soumission du formulaire
        $form = $crawler->selectButton('Ajouter')->form([
            'task[title]'   => 'Ma tâche test',
            'task[content]' => 'Contenu test',
        ]);

        $client->submit($form);
        $client->followRedirect();

        // Vérification BDD
        $task = $em->getRepository(Task::class)->findOneBy(['title' => 'Ma tâche test']);

        $this->assertNotNull($task, "La tâche n'a pas été créée");
        $this->assertEquals($user->getId(), $task->getUser()->getId(), "L'utilisateur devrait être l'auteur");
    }


    public function testAuthorCanDeleteTask()
    {
        $client = static::createClient();
        $container = self::$kernel->getContainer();
        $em = $container->get('doctrine')->getManager();

        $user = $em->getRepository(User::class)->findOneBy(['username' => 'user']);
        $this->loginAsUser($client, $user);

        // Créer une tâche
        $task = new Task();
        $task->setTitle('Delete Test');
        $task->setContent('To be deleted');
        $task->setUser($user);

        $em->persist($task);
        $em->flush();

        // Récupérer l'id AVANT clear()
        $taskId = $task->getId();

        // Réinitialise l'EntityManager pour éviter l'erreur Doctrine
        $em->clear();

        // Recharger la tâche depuis Doctrine
        $task = $em->getRepository(Task::class)->find($taskId);

        // Exécuter la suppression via l’URL
        $client->request('GET', '/tasks/' . $taskId . '/delete');

        // Vérifier que la tâche est bien supprimée
        $deleted = $em->getRepository(Task::class)->find($taskId);

        $this->assertNull($deleted, "L'auteur devrait pouvoir supprimer sa tâche");
    }


    public function testNonAuthorCannotDeleteTask()
    {
        $client = static::createClient();
        $container = self::$kernel->getContainer();
        $em = $container->get('doctrine')->getManager();

        $author = $em->getRepository(User::class)->findOneBy(['username' => 'user']);
        $intruder = $em->getRepository(User::class)->findOneBy(['username' => 'admin']); // admin doit être bloqué aussi pour les tâches des autres

        // Tâche appartenant à user
        $task = new Task();
        $task->setTitle('Protected Task');
        $task->setContent('Nobody can delete except author');
        $task->setUser($author);

        $em->persist($task);
        $em->flush();

        // Connexion en tant qu'admin → ne doit pas supprimer
        $this->loginAsUser($client, $intruder);

        $client->request('GET', '/tasks/'.$task->getId().'/delete');

        $stillExists = $em->getRepository(Task::class)->find($task->getId());
        $this->assertNotNull($stillExists, "Un utilisateur non auteur NE doit PAS pouvoir supprimer la tâche");
    }

    public function testAdminCanDeleteAnonymousTask()
    {
        $client    = static::createClient();
        $container = self::$kernel->getContainer();
        $em        = $container->get('doctrine')->getManager();

        // Récupérer l'admin et l'utilisateur "anonyme" depuis les fixtures
        $admin     = $em->getRepository(User::class)->findOneBy(['username' => 'admin']);
        $anonymous = $em->getRepository(User::class)->findOneBy(['username' => 'anonyme']);

        $this->assertNotNull($admin, 'L\'utilisateur admin doit exister (fixtures).');
        $this->assertNotNull($anonymous, 'L\'utilisateur "anonyme" doit exister (fixtures).');

        // Connexion en tant qu'admin
        $this->loginAsUser($client, $admin);

        // Créer une tâche rattachée à l'utilisateur "anonyme"
        $task = new Task();
        $task->setTitle('Anonymous Task');
        $task->setContent('Admin can delete');
        $task->setUser($anonymous);

        $em->persist($task);
        $em->flush();

        $taskId = $task->getId();
        $em->clear();

        // L'admin tente de supprimer la tâche
        $client->request('GET', '/tasks/'.$taskId.'/delete');

        // Vérifier que la tâche est bien supprimée
        $deleted = $em->getRepository(Task::class)->find($taskId);
        $this->assertNull($deleted, 'Admin doit pouvoir supprimer les tâches anonymes');
    }

    public function testTaskCreatePageShowsForm()
    {
        $client = static::createClient();
        $em = self::$kernel->getContainer()->get('doctrine')->getManager();

        $user = $em->getRepository(User::class)->findOneBy(['username' => 'user']);
        $this->loginAsUser($client, $user);

        $crawler = $client->request('GET', '/tasks/create');

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertGreaterThan(0, $crawler->filter('input[name="task[title]"]')->count());
        $this->assertGreaterThan(0, $crawler->filter('textarea[name="task[content]"]')->count());
    }

    public function testAuthorCanEditTask()
    {
        $client = static::createClient();
        $em = self::$kernel->getContainer()->get('doctrine')->getManager();

        $user = $em->getRepository(User::class)->findOneBy(['username' => 'user']);
        $this->loginAsUser($client, $user);

        // Créer une tâche
        $task = new Task();
        $task->setTitle('Old Title');
        $task->setContent('Old Content');
        $task->setUser($user);

        $em->persist($task);
        $em->flush();

        $crawler = $client->request('GET', '/tasks/'.$task->getId().'/edit');

        $form = $crawler->selectButton('Modifier')->form([
            'task[title]' => 'New Title',
            'task[content]' => 'New Content',
        ]);

        $client->submit($form);
        $client->followRedirect();

        $updated = $em->getRepository(Task::class)->find($task->getId());

        $this->assertEquals('New Title', $updated->getTitle());
        $this->assertEquals('New Content', $updated->getContent());
    }

    public function testNonAuthorCannotEditTask()
    {
        $client = static::createClient();
        $em = self::$kernel->getContainer()->get('doctrine')->getManager();

        $author = $em->getRepository(User::class)->findOneBy(['username' => 'user']);
        $intruder = $em->getRepository(User::class)->findOneBy(['username' => 'admin']);

        $task = new Task();
        $task->setTitle('Protected');
        $task->setContent('Cannot edit');
        $task->setUser($author);

        $em->persist($task);
        $em->flush();

        // Connexion admin → ne doit pas pouvoir éditer
        $this->loginAsUser($client, $intruder);

        $client->request('GET', '/tasks/'.$task->getId().'/edit');

        $this->assertEquals(403, $client->getResponse()->getStatusCode());
    }
}
