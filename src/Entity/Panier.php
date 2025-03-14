<?php

namespace App\Entity;

use App\Repository\PanierProduitsRepository;
use App\Repository\PanierRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PanierRepository::class)]
class Panier
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'paniers')]
    private ?Users $user = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column]
    private ?int $etat = null;

    /**
     * @var Collection<int, PanierProduits>
     */
    #[ORM\OneToMany(targetEntity: PanierProduits::class, mappedBy: 'panier', orphanRemoval: true, cascade:['persist'])]
    private Collection $panierProduits;

    public function __construct(?Users $user)
    {
        $this->etat = 1;
        $this->createdAt = new \DateTimeImmutable();
        $this->user = $user;
        $this->panierProduits = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): ?Users
    {
        return $this->user;
    }

    public function setUser(?Users $user): static
    {
        $this->user = $user;

        return $this;
    }

    /**
     * @return Collection<int, Produit>
     */
    public function getProduits(): Collection
    {
        $panierProduits = $this->getPanierProduits();
        $produits = new ArrayCollection();

        foreach ($panierProduits as $pp) {
            $produits->add($pp->getProduit());
        }

        return $produits;
    }

    public function addProduit(Produit $produit, EntityManagerInterface $em, int $amount = 1): static
    {
        $panierProduit = new PanierProduits($amount);
        $panierProduit->setProduit($produit);
        $panierProduit->setPanier($this);

        $this->addPanierProduit($panierProduit, $em);

        return $this;
    }

    public function removeProduit(Produit $produit, EntityManagerInterface $em): static
    {
        foreach ($this->panierProduits as $pp) {
            if ($produit === $pp->getProduit()) {
                $key = $this->panierProduits->indexOf($pp);
                $panierProduit = $this->panierProduits->get($key);
                $this->removePanierProduit($panierProduit, $em);
                break;
            }
        }
        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getEtat(): ?int
    {
        return $this->etat;
    }

    public function setEtat(int $etat): static
    {
        $this->etat = $etat;

        return $this;
    }

    /**
     * @return Collection<int, PanierProduits>
     */
    public function getPanierProduits(): Collection
    {
        return $this->panierProduits;
    }

    public function addPanierProduit(PanierProduits $panierProduit, EntityManagerInterface $em): static
    {
        foreach ($this->panierProduits as $pp) {
            if ($panierProduit->getProduit() === $pp->getProduit()) {
                $key = $this->panierProduits->indexOf($pp);
                $existingPanierProduit = $this->panierProduits->get($key);

                if ($pp->getProduit()->isBulkSale()) {
                    $existingPanierProduit->setAmount($existingPanierProduit->getAmount() + $pp->getProduit()->getBulkSize());
                } else {
                    $existingPanierProduit->setAmount($existingPanierProduit->getAmount() + 1);
                }
                $em->persist($existingPanierProduit);

                return $this;
            }
        }

        $em->persist($panierProduit);
        $this->panierProduits->add($panierProduit);

        return $this;
    }

    public function removePanierProduit(PanierProduits $panierProduit, EntityManagerInterface $em): static
    {
        if ($this->panierProduits->removeElement($panierProduit)) {
            $em->remove($panierProduit);
        }

        return $this;
    }
}
