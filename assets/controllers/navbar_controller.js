import { Controller } from "@hotwired/stimulus";

/**
 *    Controller js dédié à la gestion visuelle de la navbar
 */
export default class extends Controller {
    /**
     * Fonction lancé au chargement de la page 
     */
    connect() {
        // on ajoute un écouteur d'événement sur tout le document pour fermer le menu de navigation (seulement au format mobile)
        document.addEventListener('click', e => {
            const menu = document.getElementById('mobile-links')
            if (
                menu !== null 
                && typeof e.composedPath === 'function' 
                &&  !e.composedPath().includes(menu) 
                && !e.composedPath().includes(document.getElementById('mobile-menu-btn'))
            ) {
                this.closeMobileMenu(e, menu);
            }
        })
    }

    /**
     * Fonction active seulement au format mobile qui sert à afficher
     * @param {*} e 
     * @returns 
     */
    openMobileMenu(e) {
        e.preventDefault();
        if (document.getElementById('mobile-links') !== null) {     // si les éléments du menu mobile existent déjà la fonction ne fait rien
            return;
        }
        const links = document.getElementById('links');     // on récupère les liens et on créé un nouvel élément dans lequel on créé le menu
        const linksBlock = document.createElement('div');
        linksBlock.id = "mobile-links";
        linksBlock.classList.add('flex', 'flex-col', 'gap-2', 'mt-4');
        const navBar = document.getElementById('navbar');
        linksBlock.innerHTML = links.innerHTML;
        navBar.appendChild(linksBlock);

        e.currentTarget.addEventListener('click', e => {
            this.closeMobileMenu(e, linksBlock);
        });
    }

    /**
     * Fonction pour fermer le menu mobile
     * @param {*} e  l'événement
     * @param {*} menu  le menu
     */
    closeMobileMenu(e, menu) {
        e.preventDefault();
        if (menu) {
            menu.classList.add('hidden');
            menu.remove();
        }
    }
}