import { Controller } from "@hotwired/stimulus";

export default class extends Controller {
    connect() {
        const onglets = document.querySelectorAll('.onglet');
        for (const onglet of onglets) {
            onglet.addEventListener('click', event => {
                this.changeSelectedContent(event);
            })
        }
    }

    changeSelectedContent(event) {
        const currentSelected = document.querySelector('.onglet-selected');
        const selected = event.currentTarget;
        const currentContentType = currentSelected.dataset.content;
        const targetContentType = selected.dataset.content;
        const currentContent = document.getElementById(currentContentType);
        const targetContent = document.getElementById(targetContentType);

        if (currentSelected === selected) {
            return;
        }

        selected.classList.add('onglet-selected'); 
        setTimeout(() => {
            currentSelected.classList.remove('onglet-selected');
            currentContent.classList.add('hidden');
            targetContent.classList.remove('hidden');
        }, 50);
    }

    addToCart(event) {
        event.preventDefault();
        const url = event.currentTarget.dataset.url;
        const confBox = document.getElementById('confirmation');

        fetch(url).then(response => {
            if (!response.ok) {
                confBox.classList.remove('bg-green-300/60');
                confBox.classList.remove('text-green-700');
                confBox.classList.add('text-rose-700');
                confBox.classList.add("bg-rose-300/60");
                confBox.classList.remove("hidden");
                confBox.innerText = "Erreur lors de l'ajout du produit au panier"; 
                throw new Error("Erreur lors de l'ajout du produit au panier")
            }

            return response.text();
        }).then(text => {
            confBox.classList.remove('hidden');
            confBox.innerHTML = "<i class='fa-solid fa-circle-check mr-2'></i>" + text;
            setTimeout(() => {
                confBox.classList.add('hidden');
                confBox.innerHTML = "";
            }, 3000);
        })
    }
}