<?php

namespace App\Entity;

use App\Repository\AreaRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: AreaRepository::class)]
class Area
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $nombre_area = null;

    #[ORM\OneToOne(mappedBy: 'areaID', cascade: ['persist', 'remove'])]
    private ?Usuario $usuario = null;

    /**
     * @var Collection<int, Documento>
     */
    #[ORM\OneToMany(targetEntity: Documento::class, mappedBy: 'ID_area')]
    private Collection $documentos;

    public function __construct()
    {
        $this->documentos = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNombreArea(): ?string
    {
        return $this->nombre_area;
    }

    public function setNombreArea(string $nombre_area): static
    {
        $this->nombre_area = $nombre_area;

        return $this;
    }

    public function getUsuario(): ?Usuario
    {
        return $this->usuario;
    }

    public function setUsuario(?Usuario $usuario): static
    {
        // unset the owning side of the relation if necessary
        if ($usuario === null && $this->usuario !== null) {
            $this->usuario->setAreaID(null);
        }

        // set the owning side of the relation if necessary
        if ($usuario !== null && $usuario->getAreaID() !== $this) {
            $usuario->setAreaID($this);
        }

        $this->usuario = $usuario;

        return $this;
    }

    /**
     * @return Collection<int, Documento>
     */
    public function getDocumentos(): Collection
    {
        return $this->documentos;
    }

    public function addDocumento(Documento $documento): static
    {
        if (!$this->documentos->contains($documento)) {
            $this->documentos->add($documento);
            $documento->setIDArea($this);
        }

        return $this;
    }

    public function removeDocumento(Documento $documento): static
    {
        if ($this->documentos->removeElement($documento)) {
            // set the owning side to null (unless already changed)
            if ($documento->getIDArea() === $this) {
                $documento->setIDArea(null);
            }
        }

        return $this;
    }
}
