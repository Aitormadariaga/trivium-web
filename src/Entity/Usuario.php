<?php

namespace App\Entity;

use App\Repository\UsuarioRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: UsuarioRepository::class)]
#[ORM\UniqueConstraint(name: 'UNIQ_IDENTIFIER_USERNAME', fields: ['username'])]
#[ORM\UniqueConstraint(name: 'UNIQ_IDENTIFIER_EMAIL', fields: ['email'])]
#[UniqueEntity(fields: ['username'], message: 'Ya existe una cuenta con ese nombre de usuario.')]
#[UniqueEntity(fields: ['email'], message: 'Ya existe una cuenta con ese email.')]
class Usuario implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 180)]
    #[Assert\NotBlank]
    #[Assert\Length(min: 3, max: 180)]
    private ?string $username = null;

    // ── NUEVO: Email ──────────────────────────────────────────────
    #[ORM\Column(length: 255)]
    #[Assert\NotBlank]
    #[Assert\Email(message: 'El email {{ value }} no es válido.')]
    private ?string $email = null;

    // ── NUEVO: Verificación de cuenta ─────────────────────────────
    #[ORM\Column(options: ['default' => false])]
    private bool $emailVerified = false;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $verificationToken = null;

    // ── NUEVO: Token de reset de contraseña (opcional, para el futuro) ─
    #[ORM\Column(length: 100, nullable: true)]
    private ?string $resetPasswordToken = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $resetPasswordTokenExpiresAt = null;

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
        $this->fecha_creacion = new \DateTime();
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

    public function isAdmin(): bool
    {
        return in_array('ROLE_ADMIN', $this->getRoles(), true);
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
     * Ensure the session doesn't contain actual password hashes.
     */
    public function __serialize(): array
    {
        $data = (array) $this;
        $data["\0".self::class."\0password"] = hash('crc32c', $this->password);
        return $data;
    }

    #[\Deprecated]
    public function eraseCredentials(): void {}

    // ── Email ──────────────────────────────────────────────────────

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;
        return $this;
    }

    // ── Verificación ───────────────────────────────────────────────

    public function isEmailVerified(): bool
    {
        return $this->emailVerified;
    }

    public function setEmailVerified(bool $emailVerified): static
    {
        $this->emailVerified = $emailVerified;
        return $this;
    }

    public function getVerificationToken(): ?string
    {
        return $this->verificationToken;
    }

    public function setVerificationToken(?string $verificationToken): static
    {
        $this->verificationToken = $verificationToken;
        return $this;
    }

    public function generateVerificationToken(): static
    {
        $this->verificationToken = bin2hex(random_bytes(32));
        return $this;
    }

    // ── Reset password ─────────────────────────────────────────────

    public function getResetPasswordToken(): ?string
    {
        return $this->resetPasswordToken;
    }

    public function setResetPasswordToken(?string $token): static
    {
        $this->resetPasswordToken = $token;
        return $this;
    }

    public function getResetPasswordTokenExpiresAt(): ?\DateTimeImmutable
    {
        return $this->resetPasswordTokenExpiresAt;
    }

    public function setResetPasswordTokenExpiresAt(?\DateTimeImmutable $at): static
    {
        $this->resetPasswordTokenExpiresAt = $at;
        return $this;
    }

    // ── Campos originales ──────────────────────────────────────────

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

    /**
     * @return Collection<int, Sesion>
     */
    public function getSesiones(): Collection
    {
        return $this->sesiones;
    }

    /**
     * @return Collection<int, BackupPaciente>
     */
    public function getBackupPacientes(): Collection
    {
        return $this->backupPacientes;
    }
}