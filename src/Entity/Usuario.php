<?php

namespace App\Entity;

use App\Repository\UsuarioRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

#[ORM\Entity(repositoryClass: UsuarioRepository::class)]
#[ORM\UniqueConstraint(name: 'UNIQ_IDENTIFIER_USERNAME', fields: ['username'])]
class Usuario implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 180)]
    private ?string $username = null;

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

    #[ORM\Column(options: ['default' => true])]
    private ?bool $activo = true;

    #[ORM\Column]
    private ?\DateTime $fecha_creacion = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTime $ultimo_acceso = null;

    /**
     * @var Collection<int, UsuarioPaciente>
     */
    #[ORM\OneToMany(targetEntity: UsuarioPaciente::class, mappedBy: 'usuario', orphanRemoval: true)]
    private Collection $usuarioPacientes;

    /**
     * @var Collection<int, Sesion>
     */
    #[ORM\ManyToMany(targetEntity: Sesion::class, inversedBy: 'usuarios')]
    private Collection $sesiones;

    /**
     * @var Collection<int, BackupPaciente>
     */
    #[ORM\OneToMany(targetEntity: BackupPaciente::class, mappedBy: 'usuario')]
    private Collection $backupPacientes;

    public function __construct()
    {
        $this->usuarioPacientes = new ArrayCollection();
        $this->sesiones = new ArrayCollection();
        $this->backupPacientes = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function setUsername(string $username): static
    {
        $this->username = $username;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->username;
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

    #[\Deprecated]
    public function eraseCredentials(): void
    {
        // @deprecated, to be removed when upgrading to Symfony 8
    }

    public function isActivo(): ?bool
    {
        return $this->activo;
    }

    public function setActivo(bool $activo): static
    {
        $this->activo = $activo;

        return $this;
    }

    public function getFechaCreacion(): ?\DateTime
    {
        return $this->fecha_creacion;
    }

    public function setFechaCreacion(\DateTime $fecha_creacion): static
    {
        $this->fecha_creacion = $fecha_creacion;

        return $this;
    }

    public function getUltimoAcceso(): ?\DateTime
    {
        return $this->ultimo_acceso;
    }

    public function setUltimoAcceso(?\DateTime $ultimo_acceso): static
    {
        $this->ultimo_acceso = $ultimo_acceso;

        return $this;
    }

    /**
     * @return Collection<int, UsuarioPaciente>
     */
    public function getUsuarioPacientes(): Collection
    {
        return $this->usuarioPacientes;
    }

    public function addUsuarioPaciente(UsuarioPaciente $usuarioPaciente): static
    {
        if (!$this->usuarioPacientes->contains($usuarioPaciente)) {
            $this->usuarioPacientes->add($usuarioPaciente);
            $usuarioPaciente->setUsuario($this);
        }

        return $this;
    }

    public function removeUsuarioPaciente(UsuarioPaciente $usuarioPaciente): static
    {
        if ($this->usuarioPacientes->removeElement($usuarioPaciente)) {
            // set the owning side to null (unless already changed)
            if ($usuarioPaciente->getUsuario() === $this) {
                $usuarioPaciente->setUsuario(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Sesion>
     */
    public function getSesiones(): Collection
    {
        return $this->sesiones;
    }

    public function addSesione(Sesion $sesione): static
    {
        if (!$this->sesiones->contains($sesione)) {
            $this->sesiones->add($sesione);
        }

        return $this;
    }

    public function removeSesione(Sesion $sesione): static
    {
        $this->sesiones->removeElement($sesione);

        return $this;
    }

    /**
     * @return Collection<int, BackupPaciente>
     */
    public function getBackupPacientes(): Collection
    {
        return $this->backupPacientes;
    }

    public function addBackupPaciente(BackupPaciente $backupPaciente): static
    {
        if (!$this->backupPacientes->contains($backupPaciente)) {
            $this->backupPacientes->add($backupPaciente);
            $backupPaciente->setUsuario($this);
        }

        return $this;
    }

    public function removeBackupPaciente(BackupPaciente $backupPaciente): static
    {
        if ($this->backupPacientes->removeElement($backupPaciente)) {
            // set the owning side to null (unless already changed)
            if ($backupPaciente->getUsuario() === $this) {
                $backupPaciente->setUsuario(null);
            }
        }

        return $this;
    }
}
