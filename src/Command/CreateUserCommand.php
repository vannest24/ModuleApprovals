<?php

namespace App\Command;

use App\Entity\User;
use App\Entity\Usuario;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AsCommand(
    name: 'app:create-user',
    description: 'Crea usuarios y perfiles para el sistema de Harman',
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
        // Datos: [Email, Roles, Nombre Completo, Área, Supervisor]
        $datos = [
            ['aprobador3@harman.com', ['ROLE_APROBADOR'], 'Aprobador Prod', 'Produccion', 'Gerente Planta'],
            ['aprobador2@harman.com', ['ROLE_APROBADOR'], 'Aprobador Ing', 'Ingeniería', 'Gerente Planta'],
            ['aprobador4@harman.com', ['ROLE_APROBADOR'], 'Aprobador Lanzamientos', 'Lanzamientos', 'Director Planta'],
        ];

        foreach ($datos as $d) {
            $user = new User();
            $user->setEmail($d[0]);
            $user->setRoles($d[1]);
            $user->setPassword($this->hasher->hashPassword($user, 'password123'));
            
            $this->entityManager->persist($user);

            $perfil = new Usuario();
            $perfil->setNombreCompleto($d[2]);
            $perfil->setArea($d[3]);
            $perfil->setSupervisor($d[4]);
            $perfil->setIdUserFk($user); 

            $this->entityManager->persist($perfil);
        }

        $this->entityManager->flush();
        $output->writeln('Usuarios y perfiles de Harman creados con éxito.');
        
        return Command::SUCCESS;
    }
}