<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Task;
use App\Form\TaskType;
use App\Repository\TaskRepository;
use Doctrine\ORM\EntityManagerInterface;

class TaskController extends AbstractController
{
    #[Route('/{_locale<%app.supported_locales%>}/task/new', name: 'app_task/new')]
    public function new(Request $request, EntityManagerInterface $entityManager,  string $_locale): Response
    {
        $user = $this->getUser();
        $task = new Task($user);
        $form = $this->createForm(TaskType::class, $task);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // dd($form);

            $entityManager->persist($task);
            $entityManager->flush();
            // do anything else you need here, like send an email
            $this->addFlash(
                'success',
                "Your task has been created !"
            );
            return $this->redirectToRoute('app_home', ['_locale' => $_locale]);
        }

        return $this->render('task/new.html.twig', [
            'controller_name' => 'TaskController',
            'form' => $form->createView()
        ]);
    }


    #[Route('/{_locale<%app.supported_locales%>}/task/edit/{id}', name: 'app_task/edit')]
    public function edit(Request $request, EntityManagerInterface $entityManager, TaskRepository $repository, int $id,  string $_locale): Response
    {
        $task = $repository->findOneBy(['id' => $id]);
        $form = $this->createForm(TaskType::class, $task);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // dd($form);

            $entityManager->persist($task);
            $entityManager->flush();
            // do anything else you need here, like send an email
            $this->addFlash(
                'success',
                "Your task has been edited !"
            );
            return $this->redirectToRoute('app_home', ['_locale' => $_locale]);
        }

        return $this->render('task/edit.html.twig', [
            'controller_name' => 'TaskController',
            'form' => $form->createView()
        ]);
    }
    #[Route('/{_locale<%app.supported_locales%>}/task/delete/{id}', name: 'app_task/delete')]
    public function delete(Request $request, EntityManagerInterface $entityManager, TaskRepository $repository, int $id,  string $_locale): Response
    {
        $task = $repository->findOneBy(['id' => $id]);


        $entityManager->remove($task);
        $entityManager->flush();

        $this->addFlash(
            'success',
            "Your task has been deleted !"
        );

        return $this->redirectToRoute('app_home', ['_locale' => $_locale]);
    }
}
