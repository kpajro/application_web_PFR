import { Controller } from "@hotwired/stimulus";

export default class extends Controller {
    connect() {
        const bulkCheck = document.getElementById('bo_produit_form_isBulkSale');
        const bulkForm = document.getElementById('bulk-form');
        const stockCheck = document.getElementById('bo_produit_form_isLimitedStock');
        const stockForm = document.getElementById('stock-form');

        this.displayForm(bulkCheck, bulkForm);
        this.displayForm(stockCheck, stockForm);
    }

    displayForm(check, form) {
        const input = form.querySelector('input');
        check.addEventListener('change', () => {
            if (check.checked && form.classList.contains('hidden')) {
                form.classList.remove('hidden');
                setTimeout(() => {
                    form.classList.remove('opacity-0');
                    input.required = true;
                }, 10);
            } else if (!check.checked && !form.classList.contains('hidden')) {
                form.classList.add('opacity-0');
                setTimeout(() => {
                    form.classList.add('hidden');
                    input.required = false;
                }, 150);
            }
        });
    }

    displayImgForm(event) {
        event.preventDefault();
        const imgForm = document.getElementById('img-edit-form');
        imgForm.classList.remove('hidden');
        setTimeout(() => {
            imgForm.classList.remove('scale-0');
        }, 50);
    }
}