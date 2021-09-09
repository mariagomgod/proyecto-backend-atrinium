<?php

namespace App\Entity;

use App\Repository\EmpresaRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=EmpresaRepository::class)
 * @ORM\Table(name="empresa",uniqueConstraints={@ORM\UniqueConstraint(name="empresa_nombre_unique_idx", columns={"nombre"}),@ORM\UniqueConstraint(name="empresa_email_unique_idx", columns={"email"})})
 */
class Empresa
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=40, nullable=false)
     */
    private $nombre;

    /**
     * @ORM\Column(type="string", length=50, nullable=false)
     */
    private $telefono;

    /**
     * @ORM\Column(type="string", length=200, nullable=false)
     */
    private $email;

    /**
     * @ORM\ManyToOne(targetEntity="Sector", cascade={"all"})
     * @ORM\JoinColumn(name="sector", referencedColumnName="id", nullable=false)
     */
    private $sector;

    /**
     * @ORM\Column(type="boolean", nullable=false)
     */
    private $activo;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNombre(): ?string
    {
        return $this->nombre;
    }

    public function setNombre(?string $nombre): self
    {
        $this->nombre = $nombre;

        return $this;
    }

    public function getTelefono(): ?string
    {
        return $this->telefono;
    }

    public function setTelefono(?string $telefono): self
    {
        $this->telefono = $telefono;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(?string $email): self
    {
        $this->email = $email;

        return $this;
    }

    public function getSector(): ?Sector
    {
        return $this->sector;
    }

    public function setSector(?Sector $sector): self
    {
        $this->sector = $sector;

        return $this;
    }

    public function getActivo(): ?bool
    {
        return $this->activo;
    }

    public function setActivo(bool $activo): self
    {
        $this->activo = $activo;

        return $this;
    }
}
