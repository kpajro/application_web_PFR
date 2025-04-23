import { Controller } from "@hotwired/stimulus"

export default class extends Controller {
    connect() {
        this.categorieId = window.location.pathname.split("/")[1]
        this.charger()
    }

    // confirmation des filtres
    confirmation(){
        this.charger()
    }

    // recupération des données de la route categorie
    charger(){
        fetch(`/categorie/${this.categorieId}/produits/list`, {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
            },
            body: JSON.stringify("rien")
        })
        .then(result => result.json())
        .then(data => this.afficher(data.produits))
    }

    submitted(){
        
    }

    // affichage des produits filtrés
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
              <p>Prix : ${p.prix} €</p>`
            
            this.produits.appendChild(div)
        })
    }
}