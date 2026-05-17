<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class ApprovalHomeController extends AbstractController
{
    #[Route('/approval/home', name: 'app_approval_home')]
    public function index(): Response
    {
        return $this->render('approval_home/index.html.twig', [
            'controller_name' => 'ApprovalHomeController',
        ]);
    }
}
