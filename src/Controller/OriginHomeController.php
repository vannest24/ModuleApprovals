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
            
            /** @var User $user */
            $user = $this->getUser();

            // Datos básicos del formulario
            $solicitud->setNombreDocumento($request->request->get('nombre_documento'));
            $solicitud->setNumeroRevision($request->request->get('numero_revision'));
            $solicitud->setFechaLimite(new \DateTimeImmutable($request->request->get('fecha_limite')));
            
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