<?php

namespace App\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

#[AsCommand(
    name: 'app:test-email',
    description: 'Envía un correo de prueba para verificar la configuración del MAILER_DSN.',
)]
class TestEmailCommand extends Command
{
    public function __construct(private MailerInterface $mailer)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addArgument('destinatario', InputArgument::REQUIRED, 'El correo electrónico al que se enviará la prueba.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $destinatario = $input->getArgument('destinatario');
        $output->writeln("Intentando enviar correo de prueba a: <comment>$destinatario</comment>...");

        try {
            $email = (new Email())
                ->from('vannesoto24082003@outlook.com') // ⚠️ CAMBIA ESTO por el correo que configuraste en tu MAILER_DSN
                ->to($destinatario)
                ->subject('Correo de Prueba - Sistema Harman')
                ->html('<p>¡Hola! Si estás leyendo esto, la configuración de correos de tu contenedor Docker <strong>funciona perfectamente</strong>.</p>');

            $this->mailer->send($email);

            $output->writeln('<info>¡Correo enviado exitosamente!</info>');
            
            return Command::SUCCESS;
        } catch (\Exception $e) {
            $output->writeln('<error>Error al enviar el correo:</error>');
            $output->writeln($e->getMessage());
            
            return Command::FAILURE;
        }
    }
}