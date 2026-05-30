<?php

namespace App\Entity;

use App\Repository\UsuarioRepository;
use App\Entity\User;
use App\Entity\Area;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: UsuarioRepository::class)]
class Usuario
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $nombre_completo = null;

    #[ORM\OneToOne(inversedBy: 'usuario', cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $id_user_fk = null;

    #[ORM\OneToOne(inversedBy: 'usuario', cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(name: 'area_id', nullable: false)]
    private ?Area $areaID = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNombreCompleto(): ?string
    {
        return $this->nombre_completo;
    }

    public function setNombreCompleto(string $nombre_completo): static
    {
        $this->nombre_completo = $nombre_completo;

        return $this;
    }
    public function getIdUserFk(): ?User
    {
        return $this->id_user_fk;
    }

    public function setIdUserFk(User $id_user_fk): static
    {
        $this->id_user_fk = $id_user_fk;

        return $this;
    }

    public function getAreaID(): ?Area
    {
        return $this->areaID;
    }

    public function setAreaID(?Area $areaID): static
    {
        $this->areaID = $areaID;

        return $this;
    }
}
