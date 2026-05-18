<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Request; 
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Entity\SolicitudesDcr;
use App\Entity\Aprobaciones;
use Doctrine\ORM\EntityManagerInterface;
use App\Enum\Estatus;
use App\Enum\Status;

final class ApprovalHomeController extends AbstractController
{
    #[Route('/approval/home', name: 'app_approval_home')]
    public function index(Request $request, EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();

        // 1. PROCESAR LA DECISIÓN DESDE EL MODAL (POST)
        if ($request->isMethod('POST')) {
            $id = $request->request->get('solicitud_id');
            $accion = $request->request->get('accion');
            $solicitud = $entityManager->getRepository(SolicitudesDcr::class)->find($id);

            if ($solicitud) {
                $aprobacion = $entityManager->getRepository(Aprobaciones::class)->findOneBy([
                    'solicitud' => $solicitud,
                    'aprobador' => $user
                ]);

                if ($aprobacion) {
                    if ($accion === 'aprobar') {
                        $aprobacion->setEstatus(Status::APROBADA);
                    } else {
                        $aprobacion->setEstatus(Status::RECHAZADA);
                        $solicitud->setEstatus(Estatus::RECHAZADA);
                    }
                    $aprobacion->setFechaRespuesta(new \DateTimeImmutable());
                    $aprobacion->setComentarios($request->request->get('comentarios', 'Sin comentarios.'));
                    
                    $entityManager->flush();
                    $this->addFlash('success', 'Dictamen registrado en el sistema Harman.');
                }
            }
            return $this->redirectToRoute('app_approval_home');
        }

        // 2. CARGAR LISTA DE SOLICITUDES PENDIENTES
        $pendientes = $entityManager->getRepository(SolicitudesDcr::class)
            ->createQueryBuilder('s')
            ->join('s.aprobadores', 'a')
            ->where('a.id = :userId')
            ->setParameter('userId', $user)
            ->orderBy('s.fecha_creacion', 'DESC')
            ->getQuery()
            ->getResult();

        return $this->render('approval_home/index.html.twig', [
            'solicitudes' => $pendientes,
        ]);
    }

    #[Route('/approval/detalle/{id}', name: 'app_approval_detalle')]
    public function detalle(int $id, Request $request, EntityManagerInterface $entityManager): Response
    {
        $solicitud = $entityManager->getRepository(SolicitudesDcr::class)->find($id);

        if (!$solicitud) {
            throw $this->createNotFoundException('No existe la solicitud');
        }

        if ($request->isMethod('POST')) {
            $accion = $request->request->get('accion');
            
            if ($accion === 'aprobar') {
                $solicitud->setEstatus(Estatus::APROBADA); 
            } elseif ($accion === 'rechazar') {
                $solicitud->setEstatus(Estatus::RECHAZADA);
            }

            $entityManager->flush();
            $this->addFlash('success', 'Decisión registrada correctamente.');
            return $this->redirectToRoute('app_approval_home');
        }

        return $this->render('approval_home/detalle.html.twig', [
            'solicitud' => $solicitud,
        ]);
    }
} 