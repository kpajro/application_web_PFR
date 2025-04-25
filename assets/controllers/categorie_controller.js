import { Controller } from "@hotwired/stimulus"

export default class extends Controller {
    connect() {
        this.categorieId = window.location.pathname.split("/")[1]
        this.charger()
    }

    submitted(event){
        event.preventDefault()

        const form = event.target
        const formData = new FormData(form)
        
        const json = {}
        // je fais un regex dégeu pour enlever <form[>donnée<]>, faudra fix plutard
        formData.forEach((value, key) => {
            const k = key.replace(/^form\[(.+)\]$/, "$1")
            json[k] = value
        })
        
        this.charger(json)
    }

    charger(filter = {}){
        fetch(`/categorie/${this.categorieId}/produits/list`, {
            method: "POST",
            headers: {
                "Content-Type": "application/json"
            },
            body: JSON.stringify(filter)
        })
        .then(res => res.json())
        .then(data => {
            //console.log(data.produits)
            this.afficher(data.produits)
        })
        .catch(err => console.error("Erreur", err))
    }

    afficher(produits) {
        const box = document.getElementById("produits")
        box.innerHTML = ""

        if (produits.length === 0) {
            box.innerHTML = "<p>Aucun produit trouvé.</p>"
            return
        }

        produits.forEach(p => {
            const div = document.createElement("div")
            div.className = "article"
            div.innerHTML = `
                <img src="${p.img}" alt="${p.nom}">
                <h3><a href="/produit/${p.id}/page-produit" class="text-lg text-semibold hover:underline text-indigo-700">${p.nom}</a></h3>
                <p>${p.description}</p>
                <p>Prix : ${p.prix} €</p>
            `
            box.appendChild(div)
        })
    }
}
