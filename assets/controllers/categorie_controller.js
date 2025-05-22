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
            console.log(data);
            this.afficher(data.produits, data.directory)
        })
        .catch(err => console.error("Erreur", err))
    }

    afficher(produits, directory) {
        const box = document.getElementById("produits")
        box.innerHTML = ""

        if (produits.length === 0) {
            box.innerHTML = "<p>Aucun produit trouvé.</p>"
            return
        }

        produits.forEach(p => {
            if (!p.active) {
                return;
            }
            const div = document.createElement("div")
            const icon = p.images.icon ? '../' + directory + '/' + p.images.icon : 'uploadedFiles/produitImages/default-icon.jpg';
            div.className = "p-3 w-full hover:shadow-lg shadow-indigo-600/30 rounded-xl bg-indigo-300/30 transition-all hover:bg-indigo-200/30"
            div.innerHTML = `
                <a class="max-w-full max-h-75 overflow-hidden" href="/produit/${p.id}/page-produit">
                    <img src="${icon}" alt="${p.nom}" class="h-[300px] w-[300px] mx-auto">
                </a>
                <div class="w-full my-3">
                    <div class="flex items-center justify-between mb-2"
                        <h3 class="font-semibold"><a href="/produit/${p.id}/page-produit" class="transition-all text-lg text-semibold hover:underline text-indigo-800 hover:text-indigo-700">${p.nom}</a></h3>
                        <p class="fon-semibold">${p.note ? p.note + '/5' : 'Pas encore d\'avis'}</p>
                    </div>
                    <p class="italic text-sm text-gray-700">${p.description}</p>
                </div>
                <p class="font-bold text-end w-full">Prix : ${p.prix} €</p>
            `
            box.appendChild(div)
        })
    }
}
