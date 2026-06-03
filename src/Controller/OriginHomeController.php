<?php

namespace App\Controller;

use App\Entity\SolicitudesDcr;
use App\Entity\Aprobaciones;
use App\Entity\Documento;
use App\Entity\User; // Asegúrate de importar la entidad User
use App\Enum\Estatus;
use App\Enum\Status;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Service\EmailNotificationService;

final class OriginHomeController extends AbstractController
{
    #[Route('/origin/home', name: 'app_origin_home')]
    public function index(): Response
    {
        return $this->render('origin_home/menu.html.twig');
    }

    #[Route('/origin/nuevo', name: 'app_origin_nuevo')]
    public function nuevo(Request $request, EntityManagerInterface $entityManager, EmailNotificationService $emailService): Response
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
            $documento = new Documento();
            
            /** @var User $user */
            $user = $this->getUser();

            // Datos básicos del formulario
            $solicitud->setNombreDocumento($request->request->get('nombre_documento'));
            $solicitud->setNumeroRevision($request->request->get('numero_revision'));
            $solicitud->setFechaLimite(new \DateTimeImmutable($request->request->get('fecha_limite')));
            $documento->setNombreDocumento($request->request->get('nombre_documento_adjunto'));
            // Nuevo campo: Sincronización con el modelo E-R
            $solicitud->setRazonCambio($request->request->get('razon_cambio'));
            
            // Gestión de Archivo Nativo (Sustituye a SharePoint)
            /** @var UploadedFile $archivoFile */
            $archivoFile = $request->files->get('documento_file');
            if ($archivoFile) {
                $nuevoNombreArchivo = uniqid().'.'.$archivoFile->guessExtension();
                try {
                    // Guarda el archivo en el directorio public/uploads/documentos
                    $archivoFile->move(
                        $this->getParameter('kernel.project_dir').'/public/uploads/documentos',
                        $nuevoNombreArchivo
                    );
                    
                    // INSTANCIAR Y RELACIONAR LA NUEVA ENTIDAD DOCUMENTO
                    $documentoEntity = new Documento();
                    $documentoEntity->setNombreDocumento($archivoFile->getClientOriginalName()); // Nombre real del archivo subido
                    $documentoEntity->setRutaArchivo($nuevoNombreArchivo); // Nombre encriptado
                    
                    // Asociar el área del usuario (Originador) al Documento
                    if ($user->getUsuario() && $user->getUsuario()->getAreaID()) {
                        $documentoEntity->setIDArea($user->getUsuario()->getAreaID());
                    }
                    
                    $solicitud->setIdDocumento($documentoEntity); // Se vincula el documento a la solicitud principal
                } catch (FileException $e) {
                    $this->addFlash('error', 'Ocurrió un error al guardar el documento en el servidor. Intenta nuevamente.');
                    return $this->redirectToRoute('app_origin_nuevo');
                }
            }
            
            // Datos automáticos
            $solicitud->setFechaCreacion(new \DateTimeImmutable());
            $solicitud->setEstatus(Estatus::ABIERTA); 
            $solicitud->setOriginador($user);

            $entityManager->persist($solicitud);

            // 2. REGISTRAR LOS APROBADORES SELECCIONADOS
            $idsAprobadores = $request->request->all('aprobadores');
            
            if (!is_array($idsAprobadores)) {
                $idsAprobadores = [];
            }

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

            $entityManager->flush();

            // 3. ENVIAR CORREOS A LOS APROBADORES
            try {
                $emailService->enviarNotificacionNuevaSolicitud($solicitud);
            } catch (\Exception $e) {
                // Si Outlook falla temporalmente bloqueando la conexión, evitamos que la aplicación crashee
            }

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

    #[Route('/origin/detalle/{id}', name: 'app_origin_detalle')]
    public function detalle(int $id, EntityManagerInterface $entityManager): Response
    {
        $solicitud = $entityManager->getRepository(SolicitudesDcr::class)->find($id);

        if (!$solicitud) {
            throw $this->createNotFoundException('La solicitud DCR no fue encontrada.');
        }

        return $this->render('origin_home/detalle.html.twig', [
            'solicitud' => $solicitud,
        ]);
    }

    #[Route('/origin/editar/{id}', name: 'app_origin_editar')]
    public function editar(int $id, Request $request, EntityManagerInterface $entityManager): Response
    {
        $solicitud = $entityManager->getRepository(SolicitudesDcr::class)->find($id);

        if (!$solicitud) {
            throw $this->createNotFoundException('La solicitud DCR no fue encontrada.');
        }

        // Obtener estatus de forma segura (verifica si es objeto Enum o string)
        $estatusActual = strtolower(is_object($solicitud->getEstatus()) ? $solicitud->getEstatus()->value : $solicitud->getEstatus());
        
        if ($estatusActual !== 'abierta' && $estatusActual !== 'pendiente') {
            $this->addFlash('error', 'No es posible editar una solicitud que ya está ' . $estatusActual . '.');
            return $this->redirectToRoute('app_origin_detalle', ['id' => $id]);
        }

        // NUEVA REGLA DE NEGOCIO: Verificar si algún aprobador ya respondió
        $algunaRespuesta = false;
        foreach ($solicitud->getAprobaciones() as $aprobacion) {
            $estadoAprobacion = strtolower(is_object($aprobacion->getEstatus()) ? $aprobacion->getEstatus()->value : $aprobacion->getEstatus());
            if ($estadoAprobacion !== 'pendiente') {
                $algunaRespuesta = true;
                break;
            }
        }
        
        if ($algunaRespuesta) {
            $this->addFlash('error', 'No es posible editar la solicitud porque uno o más aprobadores ya han emitido una respuesta.');
            return $this->redirectToRoute('app_origin_detalle', ['id' => $id]);
        }

        $aprobadores = $entityManager->getRepository(User::class)
            ->createQueryBuilder('u')
            ->where('u.roles LIKE :role')
            ->setParameter('role', '%ROLE_APROBADOR%')
            ->getQuery()
            ->getResult();

        if ($request->isMethod('POST')) {
            $solicitud->setNombreDocumento($request->request->get('nombre_documento'));
            $solicitud->setNumeroRevision($request->request->get('numero_revision'));
            $solicitud->setFechaLimite(new \DateTimeImmutable($request->request->get('fecha_limite')));
            $solicitud->setRazonCambio($request->request->get('razon_cambio'));

            $documentoEntity = $solicitud->getIdDocumento();
            if ($documentoEntity) {
                $documentoEntity->setNombreDocumento($request->request->get('nombre_documento_adjunto'));
            }

            // Gestión de Archivo Nativo (Solo si se sube uno nuevo para reemplazarlo)
            /** @var UploadedFile $archivoFile */
            $archivoFile = $request->files->get('documento_file');
            if ($archivoFile) {
                $nuevoNombreArchivo = uniqid().'.'.$archivoFile->guessExtension();
                try {
                    $archivoFile->move($this->getParameter('kernel.project_dir').'/public/uploads/documentos', $nuevoNombreArchivo);
                    if (!$documentoEntity) {
                        $documentoEntity = new Documento();
                        $solicitud->setIdDocumento($documentoEntity);
                    }
                    $nombreAdjunto = $request->request->get('nombre_documento_adjunto');
                    $documentoEntity->setNombreDocumento($nombreAdjunto ? $nombreAdjunto : $archivoFile->getClientOriginalName());
                    $documentoEntity->setRutaArchivo($nuevoNombreArchivo);
                } catch (FileException $e) {
                    $this->addFlash('error', 'Ocurrió un error al guardar el documento actualizado.');
                }
            }

            // Gestionar Aprobadores (Elimina los deseleccionados y añade los nuevos)
            $idsAprobadoresSeleccionados = $request->request->all('aprobadores') ?? [];
            $aprobacionesExistentes = $solicitud->getAprobaciones();
            $aprobadoresActualesIds = [];
            
            foreach ($aprobacionesExistentes as $aprobacion) {
                $aprobadorId = $aprobacion->getAprobador()->getId();
                $aprobadoresActualesIds[] = $aprobadorId;
                if (!in_array($aprobadorId, $idsAprobadoresSeleccionados)) {
                    $entityManager->remove($aprobacion);
                    $solicitud->removeAprobadore($aprobacion->getAprobador());
                }
            }
            foreach ($idsAprobadoresSeleccionados as $idNuevoAprobador) {
                if (!in_array($idNuevoAprobador, $aprobadoresActualesIds)) {
                    $userAprobador = $entityManager->getRepository(User::class)->find($idNuevoAprobador);
                    if ($userAprobador) {
                        $nuevaAprobacion = new Aprobaciones();
                        $nuevaAprobacion->setSolicitud($solicitud);
                        $nuevaAprobacion->setAprobador($userAprobador);
                        $nuevaAprobacion->setEstatus(Status::PENDIENTE);
                        $nuevaAprobacion->setComentarios('Pendiente de revisión (Añadido en edición)');
                        $entityManager->persist($nuevaAprobacion);
                        $solicitud->addAprobadore($userAprobador);
                    }
                }
            }

            $entityManager->flush();
            $this->addFlash('registro_exitoso', 'La solicitud DCR se actualizó correctamente.');
            return $this->redirectToRoute('app_origin_detalle', ['id' => $solicitud->getId()]);
        }

        return $this->render('origin_home/editar.html.twig', [
            'solicitud' => $solicitud,
            'lista_aprobadores' => $aprobadores,
        ]);
    }

    #[Route('/origin/eliminar/{id}', name: 'app_origin_eliminar', methods: ['POST'])]
    public function eliminar(int $id, Request $request, EntityManagerInterface $entityManager): Response
    {
        $solicitud = $entityManager->getRepository(SolicitudesDcr::class)->find($id);

        if (!$solicitud) {
            throw $this->createNotFoundException('La solicitud DCR no fue encontrada.');
        }

        if ($this->isCsrfTokenValid('delete'.$solicitud->getId(), $request->request->get('_token'))) {
            // Verificar nuevamente la regla de negocio para evitar alteraciones externas
            $algunaRespuesta = false;
            foreach ($solicitud->getAprobaciones() as $aprobacion) {
                $estadoAprobacion = strtolower(is_object($aprobacion->getEstatus()) ? $aprobacion->getEstatus()->value : $aprobacion->getEstatus());
                if ($estadoAprobacion !== 'pendiente') {
                    $algunaRespuesta = true;
                    break;
                }
            }
            
            if ($algunaRespuesta) {
                $this->addFlash('error', 'No es posible eliminar la solicitud porque uno o más aprobadores ya han emitido una respuesta.');
                return $this->redirectToRoute('app_origin_detalle', ['id' => $id]);
            }

            // Eliminar las aprobaciones asociadas
            foreach ($solicitud->getAprobaciones() as $aprobacion) {
                $entityManager->remove($aprobacion);
            }

            // Eliminar la solicitud principal
            $entityManager->remove($solicitud);
            $entityManager->flush();

            $this->addFlash('registro_exitoso', 'La solicitud y sus aprobaciones han sido eliminadas correctamente.');
        } else {
            $this->addFlash('error', 'Token de seguridad inválido.');
            return $this->redirectToRoute('app_origin_detalle', ['id' => $id]);
        }

        return $this->redirectToRoute('app_origin_historial');
    }
}