import { Controller } from "@hotwired/stimulus"

export default class extends Controller {
    connect() {
        this.categorieId = window.location.pathname.split("/")[1]
        this.recherche = document.querySelector('[data-categorie-target="recherche"]')
        this.prixMin = document.querySelector('[data-categorie-target="prixMin"]')
        this.prixMax = document.querySelector('[data-categorie-target="prixMax"]')
        this.ordreAlpha = document.querySelector('[data-categorie-target="ordreAlpha"]')
        this.produits = document.querySelector('[data-categorie-target="produits"]')
        this.erreur = document.querySelector('[data-categorie-target="erreur"]')
        this.charger()
    }

    rechercher() {
        this.charger()
    }

    prix() {
        this.charger()
    }

    tri() {
        this.charger()
    }

    charger() {
        const pathname = window.location.pathname.split("/")
        const categorieId = pathname[1]
        const filtres = this.filtres()
    
        fetch(`/categorie/${categorieId}/produits/list`, {
          method: "POST",
          headers: {
            "Content-Type": "application/json",
          },
          body: JSON.stringify(filtres)
        })
          .then(res => res.json())
          .then(data => {
            this.afficher(data.produits)
          })
          .catch(err => console.error("Erreur de récupération des produits", err));
    }

    filtres() {
        return {
            prixMin: parseFloat(this.prixMin.value) || 0,
            prixMax: parseFloat(this.prixMax.value) || Infinity,
            orderBy: this.ordreAlpha.value,
            asc: this.ordreAlpha.value === "asc" ? "true" : "false",
            recherche: this.recherche.value.toLowerCase(),
        }
    }

    afficher(produits) {
        const box = this.produits
        box.innerHTML = ""

        if (produits.length === 0) {
            box.innerHTML = "<p>Aucun produit trouvé pour cette catégorie.</p>"
            return
        }

        produits.forEach(p => {
            const div = document.createElement("div")
            div.className = "article"
            div.innerHTML = `
              <img src="${p.image}" alt="${p.nom}" />
              <h3>${p.nom}</h3>
              <p>${p.description}</p>
              <p>Prix : ${p.prix} €</p>
            `
            this.produits.appendChild(div)
        })
    }
}