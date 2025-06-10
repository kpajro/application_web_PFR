import { Controller } from "@hotwired/stimulus";

/**
 * Controller js pour la gestion de l'interface de la page produit
 */
export default class extends Controller {
    /**
     * Fonction lancée au chargement, ajoute un ecouteur d'événement sur chaque onglet
     */
    connect() {
        const onglets = document.querySelectorAll('.onglet');
        for (const onglet of onglets) {
            onglet.addEventListener('click', event => {
                this.changeSelectedContent(event);
            })
        }
    }

    /**
     * Fonction pour changer le contenu affiché (description détaillée, fiche techinque ou commentaires)
     * @param {*} event 
     */
    changeSelectedContent(event) {
        const currentSelected = document.querySelector('.onglet-selected');
        const selected = event.currentTarget;
        const currentContentType = currentSelected.dataset.content;
        const targetContentType = selected.dataset.content;
        const currentContent = document.getElementById(currentContentType);
        const targetContent = document.getElementById(targetContentType);

        if (currentSelected === selected) { // si on clique sur l'onglet du contenu déjà affiché, rien ne se passe
            return;
        }

        selected.classList.add('onglet-selected'); 
        setTimeout(() => {
            currentSelected.classList.remove('onglet-selected');
            currentContent.classList.add('hidden');
            targetContent.classList.remove('hidden');
        }, 50);
    }

    /**
     * fonction d'événement pour ajouter au panier un produit
     * @param {*} event 
     */
    addToCart(event) {
        event.preventDefault();
        const url = event.currentTarget.dataset.url;        // route d'ajout au panier
        const confBox = document.getElementById('confirmation');

        fetch(url).then(response => {
            if (!response.ok) {     // si erreur, on affiche un petit message d'erreur
                confBox.classList.remove('bg-green-300/60');
                confBox.classList.remove('text-green-700');
                confBox.classList.add('text-rose-700');
                confBox.classList.add("bg-rose-300/60");
                confBox.classList.remove("hidden");
                confBox.innerText = "Erreur lors de l'ajout du produit au panier"; 
                throw new Error("Erreur lors de l'ajout du produit au panier")
            }

            return response.text();
        }).then(text => {       // si réussité, petit message de confirmation
            confBox.classList.remove('hidden');
            confBox.innerHTML = "<i class='fa-solid fa-circle-check mr-2'></i>" + text;
            setTimeout(() => {
                confBox.classList.add('hidden');
                confBox.innerHTML = "";
            }, 3000);
        })
    }

    /**
     * fonction pour naviguer dans les images du produit
     * @param {*} event 
     * @returns 
     */
    changeImage(event) {
        event.preventDefault();

        if (event.target.classList.contains('img-displayed')) {     // si l'image voulue est la même que celle déjà affichée rien ne se passe
            return;
        }
        
        const src = event.currentTarget.querySelector('img').src;
        const display = document.getElementById('displayImg');
        const currentDisplay = document.querySelector('.img-displayed');
        const div = event.currentTarget;
        
        currentDisplay.classList.remove('img-displayed');
        div.classList.add('img-displayed');
        display.src = src;      // on récupère la source de l'image et on la met dans le display de l'image en grand
    }

    /**
     * fonction pour naviguer dans les images du produit dans la modale
     * @param {*} event 
     * @returns 
     */
    changeImageModal(event) {
        console.log('hi')
        event.preventDefault();

        if (event.target.classList.contains('img-displayed')) {     // si l'image voulue est la même que celle déjà affichée rien ne se passe
            return;
        }
        
        const src = event.currentTarget.querySelector('img').src;
        const display = document.getElementById('displayImgModal');
        const currentDisplay = document.querySelector('.img-displayed');
        const div = event.currentTarget;
        
        currentDisplay.classList.remove('img-displayed');
        div.classList.add('img-displayed');
        display.src = src;      // on récupère la source de l'image et on la met dans le display de l'image en grand
    }
}