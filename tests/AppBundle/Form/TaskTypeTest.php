<?php

namespace Tests\AppBundle\Form;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use AppBundle\Form\TaskType;

class TaskTypeTest extends WebTestCase
{
    public function testTaskFormHasExpectedFields()
    {
        $client = static::createClient();
        $formFactory = $client->getContainer()->get('form.factory');

        $form = $formFactory->create(TaskType::class);

        $this->assertTrue($form->has('title'), 'Le formulaire Task doit avoir un champ "title".');
        $this->assertTrue($form->has('content'), 'Le formulaire Task doit avoir un champ "content".');
    }
}
