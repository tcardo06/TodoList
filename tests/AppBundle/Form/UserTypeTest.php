<?php

namespace Tests\AppBundle\Form;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use AppBundle\Form\UserType;

class UserTypeTest extends WebTestCase
{
    public function testUserFormHasExpectedFields()
    {
        $client = static::createClient();
        $formFactory = $client->getContainer()->get('form.factory');

        $form = $formFactory->create(UserType::class);

        $this->assertTrue($form->has('username'), 'Le formulaire User doit avoir un champ "username".');
        $this->assertTrue($form->has('password'), 'Le formulaire User doit avoir un champ "password".');
        $this->assertTrue($form->has('email'), 'Le formulaire User doit avoir un champ "email".');
    }
}
