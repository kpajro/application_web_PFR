<?php

namespace App\Entity;

use App\Repository\FaqRepository;
use Doctrine\DBAL\Types\Types;
use Symfony\Component\Serializer\Annotation\Groups;
use ApiPlatform\Metadata\ApiResource;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: FaqRepository::class)]
#[ApiResource(
    normalizationContext: ['groups' => ['produit:read']],
    denormalizationContext: ['groups' => ['produit:write']],
    forceEager: false
)]
class Faq
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['produit:read'])]
    private ?int $id = null;

    #[ORM\Column]
    #[Groups(['produit:read'])]
    private ?int $categorie = null;

    #[ORM\Column(type: Types::JSON)]
    #[Groups(['produit:read'])]
    private array $questions = [];

    #[ORM\Column(type: Types::JSON)]
    #[Groups(['produit:read'])]
    private array $reponses = [];

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(int $id): static
    {
        $this->id = $id;

        return $this;
    }

    public function getCategorie(): ?int
    {
        return $this->categorie;
    }

    public function setCategorie(int $categorie): static
    {
        $this->categorie = $categorie;

        return $this;
    }

    public function getQuestions(): array
    {
        return $this->questions;
    }

    public function setQuestions(array $questions): static
    {
        $this->questions = $questions;

        return $this;
    }

    public function getReponses(): array
    {
        return $this->reponses;
    }

    public function setReponses(array $reponses): static
    {
        $this->reponses = $reponses;

        return $this;
    }
}
