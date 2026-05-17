<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Entity\SolicitudesDcr;
use App\Form\SolicitudType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use App\Enum\Estatus;

final class OriginHomeController extends AbstractController
{
#[Route('/origin/home', name: 'app_origin_home')]
public function index(Request $request, EntityManagerInterface $entityManager): Response
{
    // 1. Creamos una nueva instancia de la entidad
    $solicitud = new SolicitudesDcr();
    
    // 2. Creamos el formulario basado en el Type que ya diseñamos
    $form = $this->createForm(SolicitudType::class, $solicitud);
    
    // 3. Le decimos al formulario que revise si viene información en el Request
    $form->handleRequest($request);

    // 4. Si el formulario fue enviado y es válido (pasa las reglas de Harman)
    if ($form->isSubmitted() && $form->isValid()) {
        
        // 1. Asignar el usuario logueado como originador
        $solicitud->setOriginador($this->getUser());

        // 2. Asignar la fecha de creación actual
       $solicitud->setFechaCreacion(new \DateTimeImmutable());

        // 3. Asignar el estatus por defecto
        $solicitud->setEstatus(Estatus::ABIERTA);

        $entityManager->persist($solicitud);
        $entityManager->flush();

        $this->addFlash('success', 'Solicitud creada correctamente.');
        return $this->redirectToRoute('app_origin_home');
    }

    // 5. Pasamos el formulario a la vista de Twig
    return $this->render('origin_home/index.html.twig', [
        'form' => $form->createView(),
    ]);
}
}
