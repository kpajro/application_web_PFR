import { Controller } from '@hotwired/stimulus';

/**
 * Controller js pour la gestion de la page profil
 */
export default class extends Controller {
    /**
     * fonction lancée au chargement de la page qui initialise les ecouteurs d'événements sur les éléments
     */
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

    /**
     * fonction pour toggle l'affichage des informations utilisateur
     * @param {*} e 
     */
    showMore(e) {
        e.preventDefault();
        const aInfo = document.getElementById('additional-info');
        const arrow = document.getElementById('show-more-arrow');
        arrow.classList.toggle('rotate-180');
        aInfo.classList.toggle('hidden');
    }

    /**
     * fonction pour toggle l'affichage du panier en cours
     * @param {*} e 
     */
    showPanierActif(e) {
        e.preventDefault();
        const panier = document.getElementById('panier-active-info');
        const arrow = document.getElementById('panier-active-arrow');

        panier.classList.toggle('hidden');
        arrow.classList.toggle('rotate-180');
    }

    /**
     * fonction pour toggle l'affichage d'un panier en particulier
     * @param {*} e 
     */
    showPanier(e) {
        e.preventDefault();
        const id = e.currentTarget.parentElement.dataset.panierId;
        console.log(id);
        const panier = document.getElementById(`panier-${id}-info`);
        const arrow = document.getElementById(`panier-${id}-arrow`);

        panier.classList.toggle('hidden');
        arrow.classList.toggle('rotate-180');
    }

    /**
     * fonction pour toggle l'affichage des vieux paniers
     * @param {*} e 
     */
    showPanierOld(e) {
        e.preventDefault();
        const paniers = document.getElementById('paniers-old');
        const arrow = document.getElementById('panier-old-arrow');

        paniers.classList.toggle('hidden');
        arrow.classList.toggle('rotate-180');
    }
}