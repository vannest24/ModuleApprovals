<?php

namespace App\Controller;

use App\Entity\SolicitudesDcr;
use App\Entity\Aprobaciones;
use App\Entity\User; // Asegúrate de importar la entidad User
use App\Enum\Estatus;
use App\Enum\Status;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class OriginHomeController extends AbstractController
{
    #[Route('/origin/home', name: 'app_origin_home')]
    public function index(): Response
    {
        return $this->render('origin_home/menu.html.twig');
    }

    #[Route('/origin/nuevo', name: 'app_origin_nuevo')]
    public function nuevo(Request $request, EntityManagerInterface $entityManager): Response
    {
        // 1. OBTENER LA LISTA DE APROBADORES PARA EL SELECTOR
        // Buscamos usuarios que tengan ROLE_APROBADOR
        $aprobadores = $entityManager->getRepository(User::class)
            ->createQueryBuilder('u')
            ->where('u.roles LIKE :role')
            ->setParameter('role', '%ROLE_APROBADOR%')
            ->getQuery()
            ->getResult();

        if ($request->isMethod('POST')) {
            $solicitud = new SolicitudesDcr();
            
            // Datos básicos del formulario
            $solicitud->setNombreDocumento($request->request->get('nombre_documento'));
            $solicitud->setNumeroRevision($request->request->get('numero_revision'));
            $solicitud->setLinkSharepoint($request->request->get('link_sharepoint'));
            $solicitud->setFechaLimite(new \DateTimeImmutable($request->request->get('fecha_limite')));
            
            // Datos automáticos
            $solicitud->setFechaCreacion(new \DateTimeImmutable());
            $solicitud->setEstatus(Estatus::ABIERTA); 
            $solicitud->setOriginador($this->getUser());

            $entityManager->persist($solicitud);

            // 2. REGISTRAR LOS APROBADORES SELECCIONADOS
            $idsAprobadores = $request->request->all('aprobadores'); // Obtenemos el array del select

            foreach ($idsAprobadores as $idAprobador) {
                $userAprobador = $entityManager->getRepository(User::class)->find($idAprobador);
                
                if ($userAprobador) {
                    // Creamos la línea en la tabla de aprobaciones
                    $nuevaAprobacion = new Aprobaciones();
                    $nuevaAprobacion->setSolicitud($solicitud);
                    $nuevaAprobacion->setAprobador($userAprobador);
                    $nuevaAprobacion->setEstatus(Status::PENDIENTE);
                    $nuevaAprobacion->setComentarios('Pendiente de revisión inicial');
                    
                    $entityManager->persist($nuevaAprobacion);

                    // También lo agregamos a la colección ManyToMany de la solicitud
                    $solicitud->addAprobadore($userAprobador);
                }
            }

// ... después de procesar el foreach de aprobadores ...

$entityManager->flush();

        // Agregamos el mensaje flash que detectará el JavaScript
        $this->addFlash('registro_exitoso', 'Tu solicitud DCR ha sido enviada correctamente.');

        // REDIRECCIÓN: Es vital para que la sesión se actualice y se muestre el SweetAlert
        return $this->redirectToRoute('app_origin_historial');
        }

        // 3. PASAR LA VARIABLE A LA VISTA (Esto quita el error de "lista_aprobadores")
        return $this->render('origin_home/nuevo.html.twig', [
            'lista_aprobadores' => $aprobadores,
        ]);
    }
    #[Route('/origin/historial', name: 'app_origin_historial')]
    public function historial(EntityManagerInterface $entityManager): Response
    {
        // Obtenemos al usuario logueado
        $user = $this->getUser();

        // Buscamos las solicitudes creadas por este usuario (originador)
        // Ordenadas por fecha de creación descendente (la más nueva primero)
        $misSolicitudes = $entityManager->getRepository(SolicitudesDcr::class)->findBy(
            ['originador' => $user],
            ['fecha_creacion' => 'DESC']
        );

        return $this->render('origin_home/historial.html.twig', [
            'solicitudes' => $misSolicitudes,
        ]);
    }
}