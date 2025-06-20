<?php

namespace App\Entity;

use App\Repository\ProduitRepository;
use ApiPlatform\Metadata\ApiResource;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Symfony\Component\Serializer\Annotation\Groups;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ProduitRepository::class)]
#[ApiResource(
    normalizationContext: ['groups' => ['produit:read']],
    denormalizationContext: ['groups' => ['produit:write']],
    forceEager: false
)]
class Produit
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['produit:read'])]
    private ?int $id = null;

    #[ORM\Column(length: 100)]
    #[Groups(['produit:read'])]
    private ?string $nom = null;

    #[ORM\Column(length: 255)]
    #[Groups(['produit:read'])]
    private ?string $description = null;

    #[ORM\Column]
    #[Groups(['produit:read'])]
    private ?float $prix = null;

    #[ORM\ManyToOne(inversedBy: 'produits')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['produit:read'])]
    private ?Categorie $categorie = null;

    #[Groups(['produit:read'])]
    #[ORM\Column(type: Types::ARRAY, nullable: true)]
    private ?array $os = null;

    #[Groups(['produit:read'])]
    #[ORM\Column(nullable: true)]
    private ?float $note = null;

    #[Groups(['produit:read'])]
    #[ORM\Column(type: Types::ARRAY, nullable: true)]
    private ?array $langages = null;

    #[ORM\Column]
    #[Groups(['produit:read'])]
    private ?bool $isLimitedStock = false;

    #[ORM\Column(nullable: true)]
    #[Groups(['produit:read'])]
    private ?int $stock = null;

    #[ORM\Column(length: 255)]
    #[Groups(['produit:read'])]
    private ?string $editeur = null;

    #[ORM\Column]
    #[Groups(['produit:read'])]
    private ?bool $isBulkSale = false;

    #[ORM\Column(nullable: true)]
    #[Groups(['produit:read'])]
    private ?int $bulkSize = null;

    #[ORM\Column(length: 8192, nullable: true)]
    #[Groups(['produit:read'])]
    private ?string $longDescription = null;

    /**
     * @var Collection<int, PanierProduits>
     */
    #[ORM\OneToMany(targetEntity: PanierProduits::class, mappedBy: 'produit', orphanRemoval: true, cascade:['persist'])]
    private Collection $panierProduits;

    /**
     * @var Collection<int, Avis>
     */
    #[ORM\OneToMany(targetEntity: Avis::class, mappedBy: 'produit', orphanRemoval: true)]
    #[Groups(['produit:read'])]
    private Collection $avis;

    #[ORM\Column]
    #[Groups(['produit:read'])]
    private ?bool $active = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['produit:read'])]
    private ?array $images = null;

    public function __construct()
    {
        $this->panierProduits = new ArrayCollection();
        $this->avis = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getPrix(): ?float
    {
        return $this->prix;
    }

    public function setPrix(float $prix): static
    {
        $this->prix = $prix;

        return $this;
    }

    public function getCategorie(): ?Categorie
    {
        return $this->categorie;
    }

    public function setCategorie(?Categorie $categorie): static
    {
        $this->categorie = $categorie;

        return $this;
    }

    public function getOs(): ?array
    {
        return $this->os;
    }

    public function setOs(array $os): static
    {
        $this->os = $os;

        return $this;
    }

    public function getNote(): ?float
    {
        return $this->note;
    }

    public function setNote(?float $note): static
    {
        $this->note = $note;

        return $this;
    }

    public function getLangages(): ?array
    {
        return $this->langages;
    }

    public function setLangages(?array $langages): static
    {
        $this->langages = $langages;

        return $this;
    }

    public function isLimitedStock(): ?bool
    {
        return $this->isLimitedStock;
    }

    public function setIsLimitedStock(bool $isLimitedStock): static
    {
        $this->isLimitedStock = $isLimitedStock;

        return $this;
    }

    public function getStock(): ?int
    {
        return $this->stock;
    }

    public function setStock(?int $stock): static
    {
        $this->stock = $stock;

        return $this;
    }

    public function getEditeur(): ?string
    {
        return $this->editeur;
    }

    public function setEditeur(string $editeur): static
    {
        $this->editeur = $editeur;

        return $this;
    }

    public function isBulkSale(): ?bool
    {
        return $this->isBulkSale;
    }

    public function setIsBulkSale(bool $isBulkSale): static
    {
        $this->isBulkSale = $isBulkSale;

        return $this;
    }

    public function getBulkSize(): ?int
    {
        return $this->bulkSize;
    }

    public function setBulkSize(?int $bulkSize): static
    {
        $this->bulkSize = $bulkSize;

        return $this;
    }

    public function getLongDescription(): ?string
    {
        return $this->longDescription;
    }

    public function setLongDescription(?string $longDescription): static
    {
        $this->longDescription = $longDescription;

        return $this;
    }

    /**
     * @return Collection<int, PanierProduits>
     */
    public function getPanierProduits(): Collection
    {
        return $this->panierProduits;
    }

    public function addPanierProduit(PanierProduits $panierProduit): static
    {
        if (!$this->panierProduits->contains($panierProduit)) {
            $this->panierProduits->add($panierProduit);
            $panierProduit->setProduit($this);
        }

        return $this;
    }

    public function removePanierProduit(PanierProduits $panierProduit): static
    {
        if ($this->panierProduits->removeElement($panierProduit)) {
            // set the owning side to null (unless already changed)
            if ($panierProduit->getProduit() === $this) {
                $panierProduit->setProduit(null);
            }
        }

        return $this;
    }

    public function getImages(): ?array
    {
        return $this->images;
    }

    public function setImages(?array $images): static
    {
        $this->images = $images;

        return $this;
    }

    /**
     * @return Collection<int, Avis>
     */
    public function getAvis(): Collection
    {
        return $this->avis;
    }

    public function addAvis(Avis $avis): static
    {
        if (!$this->avis->contains($avis)) {
            $this->avis->add($avis);
            $avis->setProduit($this);
        }

        return $this;
    }

    public function removeAvis(Avis $avis): static
    {
        if ($this->avis->removeElement($avis)) {
            // set the owning side to null (unless already changed)
            if ($avis->getProduit() === $this) {
                $avis->setProduit(null);
            }
        }

        return $this;
    }

    public function updateNote(): static 
    {
        $avisArray = $this->getAvis();
        if (!empty($avisArray)) {
            $total = 0;
            foreach ($avisArray as $avis) {
                $total += $avis->getNote();
            }
            $this->note = $total / count($avisArray);
        } else {
            $this->note = null;
        }

        return $this;
    }

    public function isActive(): ?bool
    {
        return $this->active;
    }

    public function setActive(bool $active): static
    {
        $this->active = $active;

        return $this;
    }
}
