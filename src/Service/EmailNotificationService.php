<?php

namespace App\Service;

use App\Entity\SolicitudesDcr;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Twig\Environment;

class EmailNotificationService
{
    private MailerInterface $mailer;
    private Environment $twig;
    private string $remitente;

    public function __construct(MailerInterface $mailer, Environment $twig)
    {
        $this->mailer = $mailer;
        $this->twig = $twig;
        // Debe coincidir con el correo configurado en tu MAILER_DSN de Outlook
        $this->remitente = 'tu-correo@outlook.com'; 
    }

    public function enviarNotificacionNuevaSolicitud(SolicitudesDcr $solicitud): void
    {
        $destinatarios = [];
        
        foreach ($solicitud->getAprobadores() as $aprobador) {
            // Verifica que el usuario tenga correo y lo agrega (asume que existe un método getEmail)
            if (method_exists($aprobador, 'getEmail') && $aprobador->getEmail()) {
                $destinatarios[] = $aprobador->getEmail();
            }
        }

        if (empty($destinatarios)) {
            return; // No hay a quién enviarle el correo
        }

        $email = (new Email())
            ->from($this->remitente)
            ->to(...$destinatarios)
            ->subject('Nueva Solicitud DCR Asignada: ' . $solicitud->getNombreDocumento())
            ->html($this->twig->render('emails/nueva_solicitud.html.twig', [
                'solicitud' => $solicitud
            ]));

        $this->mailer->send($email);
    }
}
