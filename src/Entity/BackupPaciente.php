<?php

namespace App\Entity;

use App\Enum\EstadoBackup;
use App\Repository\BackupPacienteRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: BackupPacienteRepository::class)]
class BackupPaciente
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $cic = null;

    #[ORM\Column(length: 255)]
    private ?string $dni = null;

    #[ORM\Column(length: 255)]
    private ?string $nombre = null;

    #[ORM\Column(length: 255)]
    private ?string $apellido1 = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $apellido2 = null;

    #[ORM\Column(nullable: true)]
    private ?int $edad = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $genero = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $patologia = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $medicacion = null;

    #[ORM\Column]
    private ?int $intensidad = null;

    #[ORM\Column]
    private ?int $tiempo = null;

    #[ORM\Column(nullable: true)]
    private ?int $intensidad2 = null;

    #[ORM\Column(nullable: true)]
    private ?int $tiempo2 = null;

    #[ORM\Column]
    private ?\DateTime $fechaActualizacion = null;

    #[ORM\Column(enumType: EstadoBackup::class)]
    private ?EstadoBackup $estado = null;

    #[ORM\ManyToOne(inversedBy: 'backupPacientes')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Usuario $usuario = null;

    #[ORM\Column(options: ['default' => false])]
    private ?bool $sincronizado = false;

    #[ORM\Column(options: ['default' => false])]
    private ?bool $conservar = false;

    #[ORM\ManyToOne(nullable: true, onDelete: 'SET NULL')]
    private ?Paciente $paciente = null;

    #[ORM\Column(nullable: true)]
    private ?bool $aplicar = null;

    public function __construct()
    {
        $this->fechaActualizacion = new \DateTime();
    }



    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCic(): ?string
    {
        return $this->cic;
    }

    public function setCic(string $cic): static
    {
        $this->cic = $cic;

        return $this;
    }

    public function getDni(): ?string
    {
        return $this->dni;
    }

    public function setDni(string $dni): static
    {
        $this->dni = $dni;

        return $this;
    }

    public function getNombre(): ?string
    {
        return $this->nombre;
    }

    public function setNombre(string $nombre): static
    {
        $this->nombre = $nombre;

        return $this;
    }

    public function getApellido1(): ?string
    {
        return $this->apellido1;
    }

    public function setApellido1(string $apellido1): static
    {
        $this->apellido1 = $apellido1;

        return $this;
    }

    public function getApellido2(): ?string
    {
        return $this->apellido2;
    }

    public function setApellido2(?string $apellido2): static
    {
        $this->apellido2 = $apellido2;

        return $this;
    }

    public function getEdad(): ?int
    {
        return $this->edad;
    }

    public function setEdad(?int $edad): static
    {
        $this->edad = $edad;

        return $this;
    }

    public function getGenero(): ?string
    {
        return $this->genero;
    }

    public function setGenero(?string $genero): static
    {
        $this->genero = $genero;

        return $this;
    }

    public function getPatologia(): ?string
    {
        return $this->patologia;
    }

    public function setPatologia(?string $patologia): static
    {
        $this->patologia = $patologia;

        return $this;
    }

    public function getMedicacion(): ?string
    {
        return $this->medicacion;
    }

    public function setMedicacion(?string $medicacion): static
    {
        $this->medicacion = $medicacion;

        return $this;
    }

    public function getIntensidad(): ?int
    {
        return $this->intensidad;
    }

    public function setIntensidad(int $intensidad): static
    {
        $this->intensidad = $intensidad;

        return $this;
    }

    public function getTiempo(): ?int
    {
        return $this->tiempo;
    }

    public function setTiempo(int $tiempo): static
    {
        $this->tiempo = $tiempo;

        return $this;
    }

    public function getIntensidad2(): ?int
    {
        return $this->intensidad2;
    }

    public function setIntensidad2(?int $intensidad2): static
    {
        $this->intensidad2 = $intensidad2;

        return $this;
    }

    public function getTiempo2(): ?int
    {
        return $this->tiempo2;
    }

    public function setTiempo2(?int $tiempo2): static
    {
        $this->tiempo2 = $tiempo2;

        return $this;
    }

    public function getFechaActualizacion(): ?\DateTime
    {
        return $this->fechaActualizacion;
    }

    public function setFechaActualizacion(\DateTime $fechaActualizacion): static
    {
        $this->fechaActualizacion = $fechaActualizacion;

        return $this;
    }

    public function getEstado(): ?EstadoBackup
    {
        return $this->estado;
    }

    public function setEstado(EstadoBackup $estado): static
    {
        $this->estado = $estado;

        return $this;
    }

    public function getUsuario(): ?Usuario
    {
        return $this->usuario;
    }

    public function setUsuario(?Usuario $usuario): static
    {
        $this->usuario = $usuario;

        return $this;
    }

    public function isSincronizado(): ?bool
    {
        return $this->sincronizado;
    }

    public function setSincronizado(bool $sincronizado): static
    {
        $this->sincronizado = $sincronizado;

        return $this;
    }

    public function isConservar(): ?bool
    {
        return $this->conservar;
    }

    public function setConservar(bool $conservar): static
    {
        $this->conservar = $conservar;

        return $this;
    }

    public function getPaciente(): ?Paciente
    {
        return $this->paciente;
    }

    public function setPaciente(?Paciente $paciente): static
    {
        $this->paciente = $paciente;

        return $this;
    }

    public function isAplicar(): ?bool
    {
        return $this->aplicar;
    }

    public function setAplicar(?bool $aplicar): static
    {
        $this->aplicar = $aplicar;

        return $this;
    }


}
