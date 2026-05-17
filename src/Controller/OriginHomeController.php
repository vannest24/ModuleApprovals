<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class OriginHomeController extends AbstractController
{
    #[Route('/origin/home', name: 'app_origin_home')]
    public function index(): Response
    {
        return $this->render('origin_home/index.html.twig', [
            'controller_name' => 'OriginHomeController',
        ]);
    }
}
