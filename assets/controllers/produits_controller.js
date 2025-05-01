import { Controller } from "@hotwired/stimulus";

export default class extends Controller {
    connect() {
        // this.loadAvis();
    }

    changeSelectedContent(event) {
        const currentSelected = document.querySelector('.produit-content-selected');
        currentSelected.classList.remove('produit-content-selected');
        setTimeout(() => {
            event.currentTarget.classList.add('produit-content-selected');
        }, 200);
    }

    addToCart(event) {
        event.preventDefault();
        console.log(event.currentTarget);
    }
}