import { Controller } from "@hotwired/stimulus"

/* Controller js dédié à la gestion de l'affichage de la page de la liste des produits pour une catégorie. */
export default class extends Controller {
    /**
     * Fonction lancée au chargement pour charger les produits et les afficher
     */
    connect() {
        this.categorieId = window.location.pathname.split("/")[1]
        this.charger()
    }

    /**
     * fonction d'événement pour submit le form
     * @param {*} event 
     */
    submitted(event){
        event.preventDefault()

        const form = event.target
        const formData = new FormData(form)     // récupération des données du formulaire de filtres et mise dans un objet FormData pour envoyer

        const json = {}
        formData.forEach((value, key) => {      // on met les données dans un json
            const k = key.replace(/^form\[(.+)\]$/, "$1")
            json[k] = value
        })

        this.charger(json)      // les données sont chargés à partir du json créé
    }

    /**
    * fonction pour charger les produits selon les filtres
    * @param {*} filter 
    */
    charger(filter = {}){
        fetch(`/categorie/${this.categorieId}/produits/list`, {         // on fait un fetch sur la route qui renvoie le json des produits
            method: "POST",
            headers: {
                "Content-Type": "application/json"
            },
            body: JSON.stringify(filter)
        })
        .then(res => res.json())        // on récupère les produits sous forme de json
        .then(data => {
            this.afficher(data.produits, data.directory)        // fonction pour gérer l'affichage des produits
        })
        .catch(err => console.error("Erreur", err))
    }

    /**
     * fonction pour gérer l'affichage des produits
     * @param {*} produits 
     * @param {*} directory 
     * @returns 
     */
    afficher(produits, directory) {
        const box = document.getElementById("produits")         // box dans laquelle sont affichés les produits, on commence par s'assurer qu'elle est vide
        box.innerHTML = ""
        const eur = new Intl.NumberFormat('fr', {
            style: 'currency',
            currency: 'EUR',
        });

        if (produits.length === 0) {
            box.innerHTML = "<p>Aucun produit trouvé.</p>"
            return
        }

        produits.forEach(p => {     // pour chaque produits dans le json, on le formatte et on l'ajoute à la box
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
                        <p class="font-semibold">${p.note ? p.note + '/5' : 'Pas encore d\'avis'}</p>
                    </div>
                    <p class="italic text-sm text-gray-700">${p.description}</p>
                </div>
                <p class="font-bold text-end w-full">Prix : ${eur.format(p.prix)}</p>
            `
            box.appendChild(div)
        })
    }

    /**
     * fonction pour toggle l'affichage des filtres
     * @param {*} event 
     */
    showFilters(event) {
        event.preventDefault();
        const form = document.getElementById('filtres-form');
        const arrow = document.getElementById('filtres-arrow');
        if (form.classList.contains('hidden')) {
            form.classList.remove('hidden');
            setTimeout(() => {
                form.classList.remove('scale-0');
            }, 50);
        } else {
            form.classList.add('scale-0');
            setTimeout(() => {
                form.classList.add('hidden');
            }, 200);
        }
        arrow.classList.toggle('rotate-180');
    }
}
