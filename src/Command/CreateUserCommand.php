<?php

namespace App\Command;

use App\Entity\User;
use App\Entity\Usuario;
use App\Entity\Area;
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
        // Datos: [Email, Roles, Nombre Completo, Nombre del Área]
        $datos = [
            ['vanessasotoh24@gmail.com', ['ROLE_APROBADOR'], 'Aprobador Ingenieria', 'Ingenieria'],
        ];

        $areaRepo = $this->entityManager->getRepository(Area::class);
        $userRepo = $this->entityManager->getRepository(User::class);

        foreach ($datos as $d) {
            $email = $d[0];
            $roles = $d[1];
            $nombreCompleto = $d[2];
            $nombreArea = $d[3];

            // 1. Buscamos el área o la creamos si no existe
            $area = $areaRepo->findOneBy(['nombre_area' => $nombreArea]);
            if (!$area) {
                $area = new Area();
                $area->setNombreArea($nombreArea);
                $this->entityManager->persist($area);
                $this->entityManager->flush(); // Guardamos para poder usarla
            }

            // 2. Buscamos al usuario por correo para no duplicar registros si corremos el comando más de una vez
            $user = $userRepo->findOneBy(['email' => $email]);
            if (!$user) {
                $user = new User();
                $user->setEmail($email);
                $user->setRoles($roles);
                $user->setPassword($this->hasher->hashPassword($user, 'password123'));
                $this->entityManager->persist($user);

                $perfil = new Usuario();
                $perfil->setNombreCompleto($nombreCompleto);
                $perfil->setAreaID($area); // Usamos el nuevo método con la entidad Area
                $perfil->setIdUserFk($user); 
                $this->entityManager->persist($perfil);
            }
        }

        $this->entityManager->flush();
        $output->writeln('Usuarios y perfiles de Harman creados con éxito.');
        
        return Command::SUCCESS;
    }
}