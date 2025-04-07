<?php

namespace App\Entity;

use App\Repository\ParametreSiteRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ParametreSiteRepository::class)]
class ParametreSite
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $adresse_support = null;

    #[ORM\Column(length: 4096)]
    private ?string $description = null;

    #[ORM\Column(length: 255)]
    private ?string $adresse_email = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getAdresseSupport(): ?string
    {
        return $this->adresse_support;
    }

    public function setAdresseSupport(string $adresse_support): static
    {
        $this->adresse_support = $adresse_support;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getAdresseEmail(): ?string
    {
        return $this->adresse_email;
    }

    public function setAdresseEmail(string $adresse_email): static
    {
        $this->adresse_email = $adresse_email;

        return $this;
    }
}
