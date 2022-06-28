<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Task;
use App\Form\TaskType;

class TaskController extends AbstractController
{
    #[Route('/task/new', name: 'app_task/new')]
    public function new(): Response
    {
        $task = new Task();
        $form = $this->createForm(TaskType::class, $task);

        return $this->render('task/new.html.twig', [
            'controller_name' => 'TaskController',
            'form' => $form->createView()
        ]);
    }
}
