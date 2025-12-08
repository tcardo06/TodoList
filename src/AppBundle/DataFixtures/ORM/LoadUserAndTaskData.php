<?php

namespace AppBundle\DataFixtures\ORM;

use AppBundle\Entity\User;
use AppBundle\Entity\Task;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;

class LoadUserAndTaskData extends Fixture
{
    public function load(ObjectManager $manager)
    {
        // ⚠️ Remplace ces deux hash par ceux que TU as générés avec security:encode-password
        // ex : docker compose exec app php bin/console security:encode-password monmotdepasse AppBundle\\Entity\\User

        $adminPasswordHash = '$2y$13$WeNuxLNmu0BFz/rNuzwWcOxA4gUuKkLVANj/O0BjtT6sT1T5AzszS';
        $userPasswordHash  = '$2y$13$WeNuxLNmu0BFz/rNuzwWcOxA4gUuKkLVANj/O0BjtT6sT1T5AzszS';

        // Utilisateur admin
        $admin = new User();
        $admin->setUsername('admin');
        $admin->setEmail('admin@example.com');
        $admin->setPassword($adminPasswordHash);

        if (method_exists($admin, 'setRoles')) {
            $admin->setRoles(['ROLE_ADMIN']);
        }

        $manager->persist($admin);

        // Utilisateur normal
        $user = new User();
        $user->setUsername('user');
        $user->setEmail('user@example.com');
        $user->setPassword($userPasswordHash);

        if (method_exists($user, 'setRoles')) {
            $user->setRoles(['ROLE_USER']);
        }

        $manager->persist($user);

        // Utilisateur "anonyme"
        $anonymous = new User();
        $anonymous->setUsername('anonyme');
        $anonymous->setEmail('anonymous@example.com');
        $anonymous->setPassword($userPasswordHash);

        if (method_exists($anonymous, 'setRoles')) {
            $anonymous->setRoles(['ROLE_USER']);
        }

        $manager->persist($anonymous);

        // Tâche d’exemple rattachée à l'utilisateur "anonyme"
        $task = new Task();
        $task->setTitle('Tâche d’exemple');
        $task->setContent('Ceci est une tâche de démonstration rattachée à l’utilisateur anonyme.');
        $task->setUser($anonymous);

        $manager->persist($task);

        $manager->flush();
    }
}
