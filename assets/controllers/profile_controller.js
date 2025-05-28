import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    connect() {
        const btn = document.getElementById('show-more');
        btn.addEventListener('click', e => {
            this.showMore(e);
        });
    }
    showMore(e) {
        e.preventDefault();
        const aInfo = document.getElementById('additional-info');
        const arrow = document.getElementById('show-more-arrow');
        arrow.classList.toggle('rotate-180');
        aInfo.classList.toggle('hidden');
    }
}