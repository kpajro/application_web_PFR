import { Controller } from "@hotwired/stimulus";

/** 
* Controller js dédié à l'interactivité de la gestion des produits dans le back office 
*/
export default class extends Controller {
    /**
     * fonction connect lancée au chargement de la page, initialisation de l'affichage des champs pour le Bulk et Stock 
     */
    connect() {
        const bulkCheck = document.getElementById('bo_produit_form_isBulkSale');
        const bulkForm = document.getElementById('bulk-form');
        const stockCheck = document.getElementById('bo_produit_form_isLimitedStock');
        const stockForm = document.getElementById('stock-form');

        this.displayForm(bulkCheck, bulkForm);
        this.displayForm(stockCheck, stockForm);
    }

    /**
     * Fonction pour afficher ou cacher les formulaires de bulk et de stock
     * @param {*} check la case
     * @param {*} form le formulaire à afficher/cacher
     */
    displayForm(check, form) {
        const input = form.querySelector('input');
        check.addEventListener('change', () => {                                    // ecouteur d'événement mis sur les cases pour le stock et le bulk
            if (check.checked && form.classList.contains('hidden')) {               // on vérifié si la case est cochée et si le formulaire est déjà affiché
                form.classList.remove('hidden');
                setTimeout(() => {
                    form.classList.remove('opacity-0');
                    input.required = true;
                }, 10);
            } else if (!check.checked && !form.classList.contains('hidden')) {      // deuxième option d'état
                form.classList.add('opacity-0');
                setTimeout(() => {
                    form.classList.add('hidden');
                    input.required = false;
                }, 150);
            }
        });
    }

    /**
     * fonction d'événement pour afficher le formulaire d'images dans la modification de produit
     * @param {Event} event bouton qui déclenche l'événement
     */
    displayImgForm(event) {
        event.preventDefault();
        const imgForm = document.getElementById('img-edit-form');
        imgForm.classList.remove('hidden');
        setTimeout(() => {
            imgForm.classList.remove('scale-0');
        }, 50);
    }
}