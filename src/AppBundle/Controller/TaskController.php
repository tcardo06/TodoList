<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Task;
use AppBundle\Form\TaskType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class TaskController extends Controller
{
    /**
     * @Route("/tasks", name="task_list")
     */
    public function listAction()
    {
        return $this->render('task/list.html.twig', [
            'tasks' => $this->getDoctrine()->getRepository('AppBundle:Task')->findAll()
        ]);
    }

    /**
     * @Route("/tasks/create", name="task_create")
     */
    public function createAction(Request $request)
    {
        $task = new Task();
        $form = $this->createForm(TaskType::class, $task);

        $form->handleRequest($request);

        if ($form->isValid()) {
            $user = $this->getUser();

            if ($user !== null) {
                $task->setUser($user);
            }

            $em = $this->getDoctrine()->getManager();
            $em->persist($task);
            $em->flush();

            $this->addFlash('success', 'La tâche a bien été ajoutée.');

            return $this->redirectToRoute('task_list');
        }

        return $this->render('task/create.html.twig', ['form' => $form->createView()]);
    }

    /**
     * @Route("/tasks/{id}/edit", name="task_edit")
     */
    public function editAction(Task $task, Request $request)
    {
        $user = $this->getUser();
        $taskOwner = $task->getUser();

        if (!$user) {
            throw new AccessDeniedException('Vous devez être connecté pour modifier une tâche.');
        }

        if ($taskOwner === null || $taskOwner->getId() !== $user->getId()) {
            throw new AccessDeniedException('Vous ne pouvez modifier que vos propres tâches.');
        }

        $form = $this->createForm(TaskType::class, $task);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            $this->addFlash('success', 'La tâche a bien été modifiée.');

            return $this->redirectToRoute('task_list');
        }

        return $this->render('task/edit.html.twig', [
            'form' => $form->createView(),
            'task' => $task,
        ]);
    }

    /**
     * @Route("/tasks/{id}/toggle", name="task_toggle")
     */
    public function toggleTaskAction(Task $task)
    {
        $task->toggle(!$task->isDone());
        $this->getDoctrine()->getManager()->flush();

        $this->addFlash('success', sprintf(
            'La tâche %s a bien été marquée comme faite.',
            $task->getTitle()
        ));

        return $this->redirectToRoute('task_list');
    }

    /**
     * @Route("/tasks/{id}/delete", name="task_delete")
     */
    public function deleteTaskAction(Task $task)
    {
        $user = $this->getUser();

        if (!$user) {
            throw new AccessDeniedException('Vous devez être authentifié pour supprimer une tâche.');
        }

        $taskOwner = $task->getUser();

        // Détection correcte d'une tâche anonyme
        $isAnonymousTask = ($taskOwner && $taskOwner->getUsername() === 'anonyme');

        if ($isAnonymousTask) {
            // Tâche anonyme → seuls les admins peuvent supprimer
            if (!$this->isGranted('ROLE_ADMIN')) {
                throw new AccessDeniedException('Seuls les administrateurs peuvent supprimer les tâches anonymes.');
            }
        } else {
            // Tâche normale → seul l’auteur peut supprimer
            if (!$taskOwner || $taskOwner->getId() !== $user->getId()) {
                throw new AccessDeniedException('Vous ne pouvez supprimer que vos propres tâches.');
            }
        }

        $em = $this->getDoctrine()->getManager();
        $em->remove($task);
        $em->flush();

        $this->addFlash('success', 'La tâche a bien été supprimée.');

        return $this->redirectToRoute('task_list');
    }
}
