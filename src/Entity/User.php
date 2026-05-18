<?php

namespace App\Entity;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM; 
use App\Entity\Aprobaciones;
use App\Entity\SolicitudesDcr;
use App\Entity\Usuario;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
#[ORM\Entity(repositoryClass: \App\Repository\UserRepository::class)]
#[ORM\Table(name: '`user`')]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    public function getId(): ?int
    {
        return $this->id;
    }
    #[ORM\Column(length: 180, unique: true)]
    private ?string $email = null;

    /**
     * @var list<string> The user roles
     */
    #[ORM\Column]
    private array $roles = [];

    /**
     * @var string The hashed password
     */
    #[ORM\Column]
    private ?string $password = null;

    #[ORM\OneToOne(mappedBy: 'id_user_fk', cascade: ['persist', 'remove'])]
    private ?Usuario $usuario = null;

    #[ORM\OneToOne(mappedBy: 'originador', cascade: ['persist', 'remove'])]
    private ?SolicitudesDcr $solicitudesDcr = null;

    #[ORM\OneToOne(mappedBy: 'aprobador', cascade: ['persist', 'remove'])]
    private ?Aprobaciones $aprobaciones = null;

    /**
     * @var Collection<int, SolicitudesDcr>
     */
    #[ORM\ManyToMany(targetEntity: SolicitudesDcr::class, mappedBy: 'aprobadores')]
    private Collection $solicitudesDcrs;

    public function __construct()
    {
        $this->solicitudesDcrs = new ArrayCollection();
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    /**
     * @param list<string> $roles
     */
    public function setRoles(array $roles): static
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;

        return $this;
    }

    /**
     * Ensure the session doesn't contain actual password hashes by CRC32C-hashing them, as supported since Symfony 7.3.
     */
    public function __serialize(): array
    {
        $data = (array) $this;
        $data["\0".self::class."\0password"] = hash('crc32c', $this->password);

        return $data;
    }

    public function getUsuario(): ?Usuario
    {
        return $this->usuario;
    }

    public function setUsuario(Usuario $usuario): static
    {
        // set the owning side of the relation if necessary
        if ($usuario->getIdUserFk() !== $this) {
            $usuario->setIdUserFk($this);
        }

        $this->usuario = $usuario;

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
            $this->solicitudesDcr->setOriginador(null);
        }

        // set the owning side of the relation if necessary
        if ($solicitudesDcr !== null && $solicitudesDcr->getOriginador() !== $this) {
            $solicitudesDcr->setOriginador($this);
        }

        $this->solicitudesDcr = $solicitudesDcr;

        return $this;
    }

    public function getAprobaciones(): ?Aprobaciones
    {
        return $this->aprobaciones;
    }

    public function setAprobaciones(?Aprobaciones $aprobaciones): static
    {
        // unset the owning side of the relation if necessary
        if ($aprobaciones === null && $this->aprobaciones !== null) {
            $this->aprobaciones->setAprobador(null);
        }

        // set the owning side of the relation if necessary
        if ($aprobaciones !== null && $aprobaciones->getAprobador() !== $this) {
            $aprobaciones->setAprobador($this);
        }

        $this->aprobaciones = $aprobaciones;

        return $this;
    }

    /**
     * @return Collection<int, SolicitudesDcr>
     */
    public function getSolicitudesDcrs(): Collection
    {
        return $this->solicitudesDcrs;
    }

    public function addSolicitudesDcr(SolicitudesDcr $solicitudesDcr): static
    {
        if (!$this->solicitudesDcrs->contains($solicitudesDcr)) {
            $this->solicitudesDcrs->add($solicitudesDcr);
            $solicitudesDcr->addAprobadore($this);
        }

        return $this;
    }

    public function removeSolicitudesDcr(SolicitudesDcr $solicitudesDcr): static
    {
        if ($this->solicitudesDcrs->removeElement($solicitudesDcr)) {
            $solicitudesDcr->removeAprobadore($this);
        }

        return $this;
    }
    // src/Entity/User.php

public function getNombreCompleto(): string
{

    // Si existe la relación con la entidad Usuario, sacamos el nombre de ahí
    if ($this->usuario) {
        // Ajusta 'getNombre' según cómo se llame el método en tu entidad Usuario
        return $this->usuario->getNombreCompleto(); 
    }

    // Si no hay datos de usuario, mostramos el email como respaldo
    return $this->email;
}

public function getArea(): string
{
    if ($this->usuario) {
        // Ajusta 'getArea' según tu entidad Usuario
        return $this->usuario->getArea();
    }

    return 'Sin Área';
}
}
