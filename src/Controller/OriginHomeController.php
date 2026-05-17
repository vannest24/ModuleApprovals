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
    /**
     * PANTALLA PRINCIPAL: Los dos botones (Menú)
     */
    #[Route('/origin/home', name: 'app_origin_home')]
    public function index(): Response
    {
        return $this->render('origin_home/menu.html.twig');
    }

    /**
     * FORMULARIO: Crear nueva solicitud
     */
    #[Route('/origin/nuevo', name: 'app_origin_nuevo')]
    public function nuevo(Request $request, EntityManagerInterface $entityManager): Response
    {
        $solicitud = new SolicitudesDcr();
        $form = $this->createForm(SolicitudType::class, $solicitud);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Asignación de datos automáticos
            $solicitud->setOriginador($this->getUser());
            $solicitud->setFechaCreacion(new \DateTimeImmutable());
            
            // IMPORTANTE: Asegúrate que en tu Enum sea ABIERTO (o como lo definiste)
            $solicitud->setEstatus(Estatus::ABIERTA); 

            $entityManager->persist($solicitud);
            $entityManager->flush();

            $this->addFlash('success', '¡Solicitud DCR creada con éxito!');
            
            // Después de crear, lo mandamos al historial para que vea su registro
            return $this->redirectToRoute('app_origin_historial'); 
        }

        return $this->render('origin_home/nuevo.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * HISTORIAL: Ver lo que ya se envió (Aprobaciones originadas)
     */
    #[Route('/origin/historial', name: 'app_origin_historial')]
    public function historial(EntityManagerInterface $entityManager): Response
    {
        // Buscamos solo las solicitudes que pertenecen al usuario logueado
        $solicitudes = $entityManager->getRepository(SolicitudesDcr::class)->findBy(
            ['originador' => $this->getUser()],
            ['fecha_creacion' => 'DESC']
        );

        return $this->render('origin_home/historial.html.twig', [
            'solicitudes' => $solicitudes,
        ]);
    }
    
    #[Route('/origin/detalle/{id}', name: 'app_origin_detalle')]
public function detalle(int $id, EntityManagerInterface $entityManager): Response
{
    $solicitud = $entityManager->getRepository(SolicitudesDcr::class)->find($id);

    if (!$solicitud) {
        throw $this->createNotFoundException('La solicitud no existe.');
    }

    return $this->render('origin_home/detalle.html.twig', [
        'solicitud' => $solicitud,
    ]);
}
}