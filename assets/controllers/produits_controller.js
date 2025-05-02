import { Controller } from "@hotwired/stimulus";

export default class extends Controller {
    connect() {
        // this.loadAvis();
    }

    changeSelectedContent(event) {
        const currentSelected = document.querySelector('.produit-content-selected');
        if (currentSelected === event.currentTarget) {
            return;
        }
        currentSelected.classList.remove('produit-content-selected');
        setTimeout(() => {
            event.currentTarget.classList.add('produit-content-selected');
        }, 200);
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
        })
    }
}