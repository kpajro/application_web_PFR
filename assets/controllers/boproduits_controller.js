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
        check.addEventListener('change', () => {
            if (check.checked && form.classList.contains('hidden')) {
                form.classList.remove('hidden');
                setTimeout(() => {
                    form.classList.remove('opacity-0');
                }, 10);
            } else if (!check.checked && !form.classList.contains('hidden')) {
                form.classList.add('opacity-0');
                setTimeout(() => {
                    form.classList.add('hidden');
                }, 150);
            }
        });
    }
}