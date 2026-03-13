<?php

namespace App\Entity;

use App\Repository\PacienteRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PacienteRepository::class)]
class Paciente
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 25)]
    private ?string $cic = null;

    #[ORM\Column(length: 10)]
    private ?string $dni = null;

    #[ORM\Column(length: 255)]
    private ?string $nombre = null;

    #[ORM\Column(length: 255)]
    private ?string $apellido1 = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $apellido2 = null;

    #[ORM\Column]
    private ?int $edad = null;

    #[ORM\Column(length: 255, nullable: true)]
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

    /**
     * @var Collection<int, Sesion>
     */
    #[ORM\OneToMany(targetEntity: Sesion::class, mappedBy: 'paciente', orphanRemoval: true)]
    private Collection $sesiones;

    /**
     * @var Collection<int, UsuarioPaciente>
     */
    #[ORM\OneToMany(targetEntity: UsuarioPaciente::class, mappedBy: 'paciente', orphanRemoval: true)]
    private Collection $usuarioPacientes;

    public function __construct()
    {
        $this->sesiones = new ArrayCollection();
        $this->usuarioPacientes = new ArrayCollection();
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

    public function setEdad(int $edad): static
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
            $sesione->setPaciente($this);
        }

        return $this;
    }

    public function removeSesione(Sesion $sesione): static
    {
        if ($this->sesiones->removeElement($sesione)) {
            // set the owning side to null (unless already changed)
            if ($sesione->getPaciente() === $this) {
                $sesione->setPaciente(null);
            }
        }

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
            $usuarioPaciente->setPaciente($this);
        }

        return $this;
    }

    public function removeUsuarioPaciente(UsuarioPaciente $usuarioPaciente): static
    {
        if ($this->usuarioPacientes->removeElement($usuarioPaciente)) {
            // set the owning side to null (unless already changed)
            if ($usuarioPaciente->getPaciente() === $this) {
                $usuarioPaciente->setPaciente(null);
            }
        }

        return $this;
    }
}
