<?php

namespace App\Entity;

use App\Enum\Estatus;
use App\Repository\SolicitudesDcrRepository;
use App\Entity\User;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: SolicitudesDcrRepository::class)]
class SolicitudesDcr
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $nombre_documento = null;

    #[ORM\Column(length: 255)]
    private ?string $numero_revision = null;

    #[ORM\Column(length: 255)]
    private ?string $link_sharepoint = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $fecha_creacion = null;

    #[ORM\Column(type: Types::DATE_IMMUTABLE)]
    private ?\DateTimeImmutable $fecha_limite = null;

        #[ORM\Column(type: 'string', enumType: \App\Enum\Estatus::class)]
        private ?\App\Enum\Estatus $estatus = null;

        #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'solicitudesDcrs')]
        #[ORM\JoinColumn(nullable: false)]
        private ?User $originador = null;

        /**
         * @var Collection<int, Aprobaciones>
         */
        #[ORM\OneToMany(targetEntity: Aprobaciones::class, mappedBy: 'solicitud')]
        private Collection $aprobaciones;

        /**
         * @var Collection<int, User>
         */
        #[ORM\ManyToMany(targetEntity: User::class, inversedBy: 'solicitudesDcrs')]
        private Collection $aprobadores;

        public function __construct()
        {
            $this->aprobaciones = new ArrayCollection();
            $this->aprobadores = new ArrayCollection();
        }


    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNombreDocumento(): ?string
    {
        return $this->nombre_documento;
    }

    public function setNombreDocumento(string $nombre_documento): static
    {
        $this->nombre_documento = $nombre_documento;

        return $this;
    }

    public function getNumeroRevision(): ?string
    {
        return $this->numero_revision;
    }

    public function setNumeroRevision(string $numero_revision): static
    {
        $this->numero_revision = $numero_revision;

        return $this;
    }

    public function getLinkSharepoint(): ?string
    {
        return $this->link_sharepoint;
    }

    public function setLinkSharepoint(string $link_sharepoint): static
    {
        $this->link_sharepoint = $link_sharepoint;

        return $this;
    }

    public function getFechaCreacion(): ?\DateTimeImmutable
    {
        return $this->fecha_creacion;
    }

    public function setFechaCreacion(\DateTimeImmutable $fecha_creacion): static
    {
        $this->fecha_creacion = $fecha_creacion;

        return $this;
    }

    public function getFechaLimite(): ?\DateTimeImmutable
    {
        return $this->fecha_limite;
    }

    public function setFechaLimite(\DateTimeImmutable $fecha_limite): static
    {
        $this->fecha_limite = $fecha_limite;

        return $this;
    }

    public function getEstatus(): ?\App\Enum\Estatus
    {
        return $this->estatus;
    }

    public function setEstatus(\App\Enum\Estatus $estatus): static
    {
        $this->estatus = $estatus;

        return $this;
    }

    public function getOriginador(): ?User
    {
        return $this->originador;
    }

    public function setOriginador(?User $originador): static
    {
        $this->originador = $originador;

        return $this;
    }

    /**
     * @return Collection<int, Aprobaciones>
     */
    public function getAprobaciones(): Collection
    {
        return $this->aprobaciones;
    }

    public function addAprobacione(Aprobaciones $aprobacione): static
    {
        if (!$this->aprobaciones->contains($aprobacione)) {
            $this->aprobaciones->add($aprobacione);
            $aprobacione->setSolicitud($this);
        }

        return $this;
    }

    public function removeAprobacione(Aprobaciones $aprobacione): static
    {
        if ($this->aprobaciones->removeElement($aprobacione)) {
            // set the owning side to null (unless already changed)
            if ($aprobacione->getSolicitud() === $this) {
                $aprobacione->setSolicitud(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, User>
     */
    public function getAprobadores(): Collection
    {
        return $this->aprobadores;
    }

    public function addAprobadore(User $aprobadore): static
    {
        if (!$this->aprobadores->contains($aprobadore)) {
            $this->aprobadores->add($aprobadore);
        }

        return $this;
    }

    public function removeAprobadore(User $aprobadore): static
    {
        $this->aprobadores->removeElement($aprobadore);

        return $this;
    }
}
