<?php

namespace Tests\AppBundle\Entity;

use AppBundle\Entity\Task;

class TaskTest extends \PHPUnit_Framework_TestCase
{
    public function testNewTaskDefaultValues()
    {
        $task = new Task();

        // createdAt est bien une DateTime récente
        $this->assertInstanceOf(\DateTime::class, $task->getCreatedAt());
        $this->assertFalse($task->isDone());
    }

    public function testToggleTask()
    {
        $task = new Task();

        // Par défaut false
        $this->assertFalse($task->isDone());

        // On marque comme faite
        $task->toggle(true);
        $this->assertTrue($task->isDone());

        // On remet non faite
        $task->toggle(false);
        $this->assertFalse($task->isDone());
    }
}
