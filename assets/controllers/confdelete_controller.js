import { Controller } from "@hotwired/stimulus";

/**
 * controller js pour gérer la confirmation de la suppression d'un élément qui suit le bon pattern (en gros juste une confirmation)
 */
export default class extends Controller {
    /**
     * fonction lancée au chargement qui ajoute une confirmation au bon endroit
     */
    connect() {
        const footerContent = document.getElementById('footer-content');
        const deleteBtn = document.getElementById('delete-btn');
        const confBox = document.getElementById('delete-conf');
        const cancelBtn = document.getElementById('cancel-delete');

        deleteBtn.addEventListener('click', e => {
            e.preventDefault();
            footerContent.classList.add('opacity-0');
            setTimeout(() => {
                footerContent.classList.add('hidden');
                confBox.classList.remove('hidden');
                confBox.classList.remove('opacity-0');
            }, 150);

            cancelBtn.addEventListener('click', e => {
                e.preventDefault();
                confBox.classList.add('opacity-0');
                setTimeout(() => {
                    confBox.classList.add('hidden');
                    footerContent.classList.remove('hidden');
                    footerContent.classList.remove('opacity-0');
                }, 150);
            })
        })
    }
}