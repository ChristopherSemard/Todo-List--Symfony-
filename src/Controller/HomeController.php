<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\UserRepository;
use App\Repository\TaskRepository;

class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(TaskRepository $taskRrepository, UserRepository $userRrepository ): Response
    {
        $tasks = $taskRrepository->findAll();
        return $this->render('home/index.html.twig', [
            'controller_name' => 'HomeController',
            'tasks' => $tasks
        ]);
    }
}
