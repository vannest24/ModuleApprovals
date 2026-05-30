<?php

namespace App\Entity;

use App\Repository\DocumentoRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: DocumentoRepository::class)]
class Documento
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $nombre_documento = null;

    #[ORM\ManyToOne(inversedBy: 'documentos')]
    private ?Area $ID_area = null;

    #[ORM\Column(length: 255)]
    private ?string $ruta_archivo = null;

    #[ORM\OneToOne(mappedBy: 'idDocumento', cascade: ['persist', 'remove'])]
    private ?SolicitudesDcr $solicitudesDcr = null;

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

    public function getIDArea(): ?Area
    {
        return $this->ID_area;
    }

    public function setIDArea(?Area $ID_area): static
    {
        $this->ID_area = $ID_area;

        return $this;
    }

    public function getRutaArchivo(): ?string
    {
        return $this->ruta_archivo;
    }

    public function setRutaArchivo(string $ruta_archivo): static
    {
        $this->ruta_archivo = $ruta_archivo;

        return $this;
    }

    public function getSolicitudesDcr(): ?SolicitudesDcr
    {
        return $this->solicitudesDcr;
    }

    public function setSolicitudesDcr(?SolicitudesDcr $solicitudesDcr): static
    {
        // unset the owning side of the relation if necessary
        if ($solicitudesDcr === null && $this->solicitudesDcr !== null) {
            $this->solicitudesDcr->setIdDocumento(null);
        }

        // set the owning side of the relation if necessary
        if ($solicitudesDcr !== null && $solicitudesDcr->getIdDocumento() !== $this) {
            $solicitudesDcr->setIdDocumento($this);
        }

        $this->solicitudesDcr = $solicitudesDcr;

        return $this;
    }
}
