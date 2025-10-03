<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(): Response
    {
        // If user is authenticated, redirect to task list
        if ($this->getUser()) {
            return $this->redirectToRoute('app_task_list');
        }
        
        // If not authenticated, redirect to login
        return $this->redirectToRoute('app_login');
    }
}