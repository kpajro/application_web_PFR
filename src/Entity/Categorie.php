<?php

namespace App\Entity;

use App\Repository\CategorieRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Serializer\Annotation\Groups;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\ApiResource;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CategorieRepository::class)]
#[ApiResource(
    normalizationContext: ['groups' => ['categories:read'], ['categorie:read']],
    denormalizationContext: ['groups' => ['categories:write']],
    forceEager: false,
    operations: [
        new GetCollection(
            normalizationContext: ['groups' => ['categories:read']]
        ),
        new Get(
            normalizationContext: ['groups' => ['categorie:read']]
        )

    ]
)]
class Categorie
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['categories:read', 'categorie:read'])]
    private ?int $id = null;

    #[ORM\Column(length: 100)]
    #[Groups(['categories:read', 'categorie:read'])]
    private ?string $nom = null;

    /**
     * @var Collection<int, Produit>
     */
    #[ORM\OneToMany(targetEntity: Produit::class, mappedBy: 'categorie', orphanRemoval: true)]
    #[Groups(['categorie:read'])]
    private Collection $produits;

    #[ORM\Column]
    #[Groups(['categories:read', 'categorie:read'])]
    private ?int $nbProduits = null;

    public function __construct()
    {
        $this->produits = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(int $id): static
    {
        $this->id = $id;

        return $this;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(string $nom): static
    {
        $this->nom = $nom;

        return $this;
    }

    /**
     * @return Collection<int, Produit>
     */
    public function getProduits(): Collection
    {
        return $this->produits;
    }

    public function addProduit(Produit $produit): static
    {
        if (!$this->produits->contains($produit)) {
            $this->produits->add($produit);
            $produit->setCategorie($this);
        }

        return $this;
    }

    public function removeProduit(Produit $produit): static
    {
        if ($this->produits->removeElement($produit)) {
            // set the owning side to null (unless already changed)
            if ($produit->getCategorie() === $this) {
                $produit->setCategorie(null);
            }
        }

        return $this;
    }

    public function getNbProduits(): ?int
    {
        return $this->nbProduits;
    }

    public function setNbProduits(int $nbProduits): static
    {
        $this->nbProduits = $nbProduits;

        return $this;
    }
}
