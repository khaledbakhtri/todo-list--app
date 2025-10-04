<?php

namespace App\Controller;

use App\Entity\Task;
use App\Form\TaskType; // Make sure this form type exists and is correctly defined
use App\Repository\TaskRepository; // Add this if you plan to use it consistently
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted; // For easy access contro

class TaskController extends AbstractController
{
    #[Route('/tasks', name: 'app_task_list')]
    public function list(EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();
        $tasks = $entityManager->getRepository(Task::class)->findBy(['user' => $user], ['id' => 'DESC']);

        return $this->render('task/list.html.twig', [
            'tasks' => $tasks,
        ]);
    }

    #[Route('/tasks/new', name: 'app_task_new')]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $task = new Task();
        $task->setUser($this->getUser());
        
        $form = $this->createForm(TaskType::class, $task);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($task);
            $entityManager->flush();

            $this->addFlash('success', 'Tâche créée avec succès!');
            return $this->redirectToRoute('app_task_list');
        }

        return $this->render('task/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

#[Route('/tasks/{id}/edit', name: 'app_task_edit')]
public function edit(Request $request, EntityManagerInterface $entityManager, int $id): Response
{
    $task = $entityManager->getRepository(Task::class)->find($id);

    if (!$task || $task->getUser() !== $this->getUser()) {
        throw $this->createNotFoundException('Tâche non trouvée');
    }

    // Capture original data BEFORE handling request
    $originalData = [
        'title' => $task->getTitle(),
        'description' => $task->getDescription(),
        'dueDate' => $task->getDueDate()?->format('Y-m-d'),
        'isDone' => $task->isDone()
    ];

    $form = $this->createForm(TaskType::class, $task);
    $form->handleRequest($request);

    if ($form->isSubmitted()) {
        if ($form->isValid()) {
            // Compare submitted data
            $submittedData = [
                'title' => $task->getTitle(),
                'description' => $task->getDescription(),
                'dueDate' => $task->getDueDate()?->format('Y-m-d'),
                'isDone' => $task->isDone()
            ];

            if ($submittedData === $originalData) {
                $this->addFlash('info', 'Aucune modification détectée.');
            } else {
                $entityManager->flush();
                $this->addFlash('success', 'Tâche modifiée avec succès!');
            }

            return $this->redirectToRoute('app_task_list');
        } else {
            // Show form errors as flash messages
            foreach ($form->getErrors(true) as $error) {
                $this->addFlash('error', $error->getMessage());
            }
        }
    }

    return $this->render('task/edit.html.twig', [
        'form' => $form->createView(),
        'task' => $task,
    ]);
}



    #[Route('/task/delete/{id}', name: 'app_task_delete', methods: ['POST'])]
    public function delete(Request $request, EntityManagerInterface $entityManager, int $id): Response
    {
        $task = $entityManager->getRepository(Task::class)->find($id);
        if (!$task || $task->getUser() !== $this->getUser()) {
            throw $this->createNotFoundException('Tâche non trouvée');
        }

        if ($this->isCsrfTokenValid('delete'.$task->getId(), $request->request->get('_token'))) {
            $entityManager->remove($task);
            $entityManager->flush();

            if ($request->isXmlHttpRequest()) {
                return new Response('', 204);
            }

            $this->addFlash('success', 'Tâche supprimée avec succès!');
        }

        return $this->redirectToRoute('app_task_list');
    }

    #[Route('/task/toggle/{id}', name: 'app_task_toggle', methods: ['POST'])]
    public function toggle(Request $request, EntityManagerInterface $entityManager, int $id): Response
    {
        $task = $entityManager->getRepository(Task::class)->find($id);
        if (!$task || $task->getUser() !== $this->getUser()) {
            throw $this->createNotFoundException('Tâche non trouvée');
        }

        if ($this->isCsrfTokenValid('toggle'.$task->getId(), $request->request->get('_token'))) {
            $task->setIsDone(!$task->isDone());
            $entityManager->flush();
        }

        if ($request->isXmlHttpRequest()) {
            return $this->json(['success' => true, 'isDone' => $task->isDone()]);
        }

        $this->addFlash('success', 'Statut de la tâche mis à jour!');
        return $this->redirectToRoute('app_task_list');
    }
}
