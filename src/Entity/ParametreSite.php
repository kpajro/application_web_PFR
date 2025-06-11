<?php

namespace App\Entity;

use App\Repository\ParametreSiteRepository;
use ApiPlatform\Metadata\ApiResource;
use Symfony\Component\Serializer\Annotation\Groups;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ParametreSiteRepository::class)]
#[ApiResource(
    normalizationContext: ['groups' => ['produit:read']],
    denormalizationContext: ['groups' => ['produit:write']],
    forceEager: false
)]
class ParametreSite
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['produit:read'])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(['produit:read'])]
    private ?string $adresse_support = null;

    #[ORM\Column(length: 4096)]
    #[Groups(['produit:read'])]
    private ?string $description = null;

    #[ORM\Column(length: 255)]
    #[Groups(['produit:read'])]
    private ?string $adresse_email = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(int $id): static
    {
        $this->id = $id;

        return $this;
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
