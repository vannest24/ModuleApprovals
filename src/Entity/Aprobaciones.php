<?php

namespace App\Entity;

use App\Enum\Status;
use App\Repository\AprobacionesRepository;
use App\Entity\User;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: AprobacionesRepository::class)]
class Aprobaciones
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

        #[ORM\Column(type: 'string', enumType: \App\Enum\Status::class)]
        private ?\App\Enum\Status $estatus = null;

        #[ORM\Column(nullable: true)] // Agrega nullable: true
        private ?\DateTimeImmutable $fecha_respuesta = null;

    #[ORM\Column(length: 255)]
    private ?string $comentarios = null;

    #[ORM\ManyToOne(inversedBy: 'aprobaciones')]
    private ?SolicitudesDcr $solicitud = null;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'aprobaciones')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $aprobador = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEstatus(): ?\App\Enum\Status
    {
        return $this->estatus;
    }

    public function setEstatus(?\App\Enum\Status $estatus): static
    {
        $this->estatus = $estatus;

        return $this;
    }

    public function getFechaRespuesta(): ?\DateTimeImmutable
    {
        return $this->fecha_respuesta;
    }

    public function setFechaRespuesta(\DateTimeImmutable $fecha_respuesta): static
    {
        $this->fecha_respuesta = $fecha_respuesta;

        return $this;
    }

    public function getComentarios(): ?string
    {
        return $this->comentarios;
    }

    public function setComentarios(string $comentarios): static
    {
        $this->comentarios = $comentarios;

        return $this;
    }

    public function getSolicitud(): ?SolicitudesDcr
    {
        return $this->solicitud;
    }

    public function setSolicitud(?SolicitudesDcr $solicitud): static
    {
        $this->solicitud = $solicitud;

        return $this;
    }

    public function getAprobador(): ?User
    {
        return $this->aprobador;
    }

    public function setAprobador(?User $aprobador): static
    {
        $this->aprobador = $aprobador;

        return $this;
    }
}
