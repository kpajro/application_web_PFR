import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    connect() {
        const btn = document.getElementById('show-more');
        const panierActifBtn = document.getElementById('panier-actif-btn');
        const paniers = [...document.querySelectorAll('.panier-old')];
        const paniersOldBtn = document.getElementById('panier-old-btn');

        btn.addEventListener('click', e => {
            this.showMore(e);
        });
        panierActifBtn.addEventListener('click', e => {
            this.showPanierActif(e);
        });
        paniers.forEach(panier => {
            panier.addEventListener('click', e => {
                this.showPanier(e);
            });
        });
        paniersOldBtn.addEventListener('click', e => {
            this.showPanierOld(e);
        });
    }

    showMore(e) {
        e.preventDefault();
        const aInfo = document.getElementById('additional-info');
        const arrow = document.getElementById('show-more-arrow');
        arrow.classList.toggle('rotate-180');
        aInfo.classList.toggle('opacity-0');
    }

    showPanierActif(e) {
        e.preventDefault();
        const panier = document.getElementById('panier-active-info');
        const arrow = document.getElementById('panier-active-arrow');

        panier.classList.toggle('hidden');
        arrow.classList.toggle('rotate-180');
    }

    showPanier(e) {
        e.preventDefault();
        const id = e.currentTarget.parentElement.dataset.panierId;
        console.log(id);
        const panier = document.getElementById(`panier-${id}-info`);
        const arrow = document.getElementById(`panier-${id}-arrow`);

        panier.classList.toggle('hidden');
        arrow.classList.toggle('rotate-180');
    }

    showPanierOld(e) {
        e.preventDefault();
        const paniers = document.getElementById('paniers-old');
        const arrow = document.getElementById('panier-old-arrow');

        paniers.classList.toggle('hidden');
        arrow.classList.toggle('rotate-180');
    }
}