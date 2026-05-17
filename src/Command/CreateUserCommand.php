<?php

namespace App\Command;

use App\Entity\User; 
use Doctrine\ORM\EntityManagerInterface; 
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AsCommand(
    name: 'app:create-user',
    description: 'Crea usuarios iniciales para el sistema de Harman',
)]

class CreateUserCommand extends Command
{

    public function __construct(
        private UserPasswordHasherInterface $hasher,
        private EntityManagerInterface $entityManager
    ) {
        parent::__construct();
    }

protected function execute(InputInterface $input, OutputInterface $output): int
{
    $usuarios = [
        ['admin@harman.com', ['ROLE_ADMIN']],
        ['originador@harman.com', ['ROLE_ORIGINADOR']],
        ['aprobador@harman.com', ['ROLE_APROBADOR']],
    ];

    foreach ($usuarios as $u) {
        $user = new User();
        $user->setEmail($u[0]);
        $user->setRoles($u[1]);
        // Hasheamos la contraseña "password123"
        $user->setPassword($this->hasher->hashPassword($user, 'password123'));
        
        $this->entityManager->persist($user);
    }

    $this->entityManager->flush();
    $output->writeln('Usuarios creados con éxito. Password: password123');
    return Command::SUCCESS;
}
}
