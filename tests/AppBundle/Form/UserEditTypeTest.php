<?php

namespace Tests\AppBundle\Form;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use AppBundle\Form\UserEditType;

class UserEditTypeTest extends WebTestCase
{
    public function testUserEditFormHasExpectedFields()
    {
        $client = static::createClient();
        $formFactory = $client->getContainer()->get('form.factory');

        $form = $formFactory->create(UserEditType::class);

        $this->assertTrue($form->has('username'), 'Le formulaire d\'édition doit avoir "username".');
        $this->assertTrue($form->has('password'), 'Le formulaire d\'édition doit avoir "password".');
        $this->assertTrue($form->has('email'), 'Le formulaire d\'édition doit avoir "email".');
        $this->assertTrue($form->has('roles'), 'Le formulaire d\'édition doit permettre de gérer les rôles.');
    }
}
