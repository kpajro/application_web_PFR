<?php

namespace App\Entity;

use App\Repository\PanierProduitsRepository;
use ApiPlatform\Metadata\ApiResource;
use Symfony\Component\Serializer\Annotation\Groups;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PanierProduitsRepository::class)]
#[ApiResource(
    normalizationContext: ['groups' => ['produit:read']],
    denormalizationContext: ['groups' => ['produit:write']],
    forceEager: false
)]
class PanierProduits
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['produit:read'])]
    private ?int $id = null;

    #[ORM\Column]
    #[Groups(['produit:read'])]
    private ?int $amount = null;

    #[ORM\ManyToOne(inversedBy: 'panierProduits', cascade:['persist'])]
    #[ORM\JoinColumn(nullable: false)]
    private ?Produit $produit = null;

    #[ORM\ManyToOne(inversedBy: 'panierProduits', cascade:['persist'])]
    #[ORM\JoinColumn(nullable: false)]
    private ?Panier $panier = null;

    public function __construct(int $amount = 1)
    {
        $this->amount = $amount;
    }
    public function getId(): ?int
    {
        return $this->id;
    }

    public function getAmount(): ?int
    {
        return $this->amount;
    }

    public function setAmount(int $amount): static
    {
        $this->amount = $amount;

        return $this;
    }

    public function getProduit(): ?Produit
    {
        return $this->produit;
    }

    public function setProduit(?Produit $produit): static
    {
        $this->produit = $produit;

        return $this;
    }

    public function getPanier(): ?Panier
    {
        return $this->panier;
    }

    public function setPanier(?Panier $panier): static
    {
        $this->panier = $panier;

        return $this;
    }
}
