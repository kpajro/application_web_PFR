import { Controller } from "@hotwired/stimulus";
import ClassicEditor from '@ckeditor/ckeditor5-build-classic';

/**
 *  Controller js qui gère tout ce qui est en rapport avec les modales utilisées partout sur le site
 *  le code est designé de façon a pouvoir être utilisé partout sur le site et par n'importe qui grâce à différents paramètres à renseigner
 */
export default class extends Controller 
{
    connect() {
        
    }

    /**
     *   fonction d'événement, se lance quand on clique sur un élément ciblé et sert à ouvrir la modale et injecter le contenu dedans
     *   @param {Event} event 
     */
    open(event) {
        event.preventDefault();
        const url = event.currentTarget.dataset.modalUrl;       // url du contenu ciblé à renseigner sur l'élément html (data-modal-url)
        const size = event.currentTarget.dataset.modalSize;     // taille de la modale à rensigner sur l'élément html (data-modal-size)         
        const modal = document.getElementById("modal");
        const box = document.getElementById("modal-box");
        modal.classList.remove("hidden");                       // on affiche la modale
        switch (size) {     // 4 tailles de modales possibles : small (sm), medium (md), large (lg), extra-large (xl)
            case 'sm':
                box.classList.add('max-w-3xl');     // classe tailwind ajoutée selon la taille renseignée
                break;
            case 'md':
                box.classList.add('max-w-5xl');
                break;
            case 'lg':
                box.classList.add('max-w-7xl');
                break;
            case 'xl':
                box.classList.add('max-w-[1580px]');
                break;
        }

        fetch(url, { method: "POST" })      //requête pour récupérer le contenu voulu
            .then((response) => {
                if (!response.ok) {
                    throw new Error("Erreur lors du chargement du contenu");
                }
                return response.text();
            })
            .then(html => {
                    this.element.querySelector("#modal-content").innerHTML = html;      //le contenu chargé est injecté dans la modale
                    this.closeModalHandler(modal, box);                                 //on charge les boutons pour fermer la modale
                    
                    modal.classList.remove("opacity-0");
                    box.classList.remove("translate-y-[100vh]");
                    if (box.querySelector('.ckeditor')) {       // principalement pour les modales du côté du backoffice, sert à initialiser ckeditor s'il a été chargé avec le contenu de la modale
                       this.loadCkeditor(); 
                    }
                })
            .catch(error => {       // en cas d'erreur la modale est affichée mais un message d'erreur est affiché
                console.error(error);
                this.element.querySelector("#modal-content").innerHTML =
                    "<p class='text-red-500'>Impossible de charger le contenu demandé.</p>"
                    ;
                    
                box.classList.remove("translate-y-[100vh]");
            })
        ;
    }


    /**
     *  Fonction d'événement qui sert à fermer la modale, utilisable sur tous les éléments qui servent à ça
     * @param {*} event l'événement
     * @param {*} modal la modale
     * @param {*} box la box dans laquelle le contenu est chargé
     */
    close(event, modal, box) {
        event.preventDefault();
        // Masquer la modale
        box.classList.add("translate-y-[100vh]");
        modal.classList.add('opacity-0');

        setTimeout(() => {
            modal.classList.add("hidden");
            // Nettoyer le contenu
            modal.querySelector("#modal-content").innerHTML = "";
            this.removeSize(box); // on retire la taille de la modale pour s'assurer que si en ouvre une autre elle aura la taille voulue
        }, 200);
    }

    /**
     * fonction qui recherchent tous les éléments dédiés à la fermeture de la modale et qui initialise comme tel
     * @param {*} modal la modale
     * @param {*} box le contenu de la modale
     */
    closeModalHandler(modal, box) {
        const closeButtons = this.element.querySelectorAll(".close-modal"); // recherche de boutons sur la modale
        closeButtons.forEach((button) => {
            button.addEventListener("click", (event) => {
                this.close(event, modal, box);
            });
        });
        document.addEventListener('click', e => {
            // cette partie c'est pour repérer quand on clique en dehors de la modale, repérer ça et fermer la modale            
            if (typeof e.composedPath === 'function' &&  !e.composedPath().includes(box) && !box.classList.contains('translate-y-[100vh]') && !e.composedPath().includes(document.querySelector('.ck'))) {
                this.close(e, modal, box);
            }
        })
    }

    /**
     * fonction pour retirer la classe tailwind pour la taille de la modale
     * @param {*} box la modale
     */
    removeSize(box) {
        const classes = [...box.classList];
        const sizeClass = classes.find(cls => cls.startsWith('max-w-'));
        box.classList.remove(sizeClass);
    }

    /**
     *  Fonction servant dans le cas où la modale est en plusieurs parties et que des boutons de navigation ont été ajoutés
     *  elle sert à update visuellement les boutons après un changement de vue
     */
    updateNavButtons () {
        const contentDiv = document.getElementById('content');      // vue de la modale (contenu visible)
        const contentAmount = contentDiv.dataset.contentAmount;     // quantités de vues
        const currentPosition = contentDiv.dataset.currentContent;  // position actuelle dans les vues
        const leftBtn = document.getElementById('left');
        const rightBtn = document.getElementById('right');

        if (contentAmount === currentPosition) {                    // if else pour déterminer que faire selon la position où on se trouve dans le vues
            if (!leftBtn.classList.contains('activated')) {
                leftBtn.classList.add('activated');
            }
            rightBtn.classList.remove('activated');
        } else if (currentPosition <= 1) {
            if (!rightBtn.classList.contains('activated')) {
                rightBtn.classList.add('activated');
            }
            leftBtn.classList.remove('activated');
        } else {
            if (!rightBtn.classList.contains('activated')) {
                rightBtn.classList.add('activated');
            }
            if (!leftBtn.classList.contains('activated')) {
                leftBtn.classList.add('activated');
            }
        }
    }

    /**
     * Fonction servant dans le cas où la modale est en plusieurs parties et que des boutons de navigation ont été ajoutés
     * elle sert à naviguer dans le vues disponibles
     * @param {*} event
     */
    changeWindow(event) {
        if (!event.currentTarget.classList.contains('activated')) {
            return;
        }

        const currentPosition = document.getElementById('content').dataset.currentContent;      // position actuelle dans les vues
        const contentAmount = document.getElementById('content').dataset.contentAmount;         // quantité de vues
        const currentContent = document.getElementById(currentPosition);                        // contenu affiché actuellement
        
        // on récupère la direction de navigation, la prochaine vue et la vue précédente
        const direction = event.currentTarget.id;
        const next = parseInt(currentPosition) + 1 <= parseInt(contentAmount) ? parseInt(currentPosition) + 1 : 1;
        const previous = parseInt(currentPosition) - 1 >= 1 ? parseInt(currentPosition) - 1 : parseInt(contentAmount);
        
        // on vérifie dans quel sens on va
        if (direction === 'left') {
            const newContent = document.getElementById(previous);       // si gauche on récupère le contenu précédent et on affiche ça
            currentContent.classList.add('translate-x-[100rem]');
            setTimeout(() => {
                currentContent.classList.add('hidden-imp');
                newContent.classList.remove('hidden-imp');
                setTimeout(() => {
                    newContent.classList.remove('-translate-x-[100rem]');
                }, 5);
            }, 150);           
            document.getElementById('content').dataset.currentContent = newContent.id;
        } else if (direction === 'right') {
            const newContent = document.getElementById(next);       // si droite on récupère le contenu suivant et on affiche ça
            currentContent.classList.add('-translate-x-[100rem]');
            setTimeout(() => {
                currentContent.classList.add('hidden-imp');
                newContent.classList.remove('hidden-imp');
                setTimeout(() => {
                    newContent.classList.remove('translate-x-[100rem]');
                }, 5);
            }, 150);           
            document.getElementById('content').dataset.currentContent = newContent.id;
        }


        this.updateNavButtons();
    }

    /**
     *    Fonction pour initialiser ckeditor
     */
    loadCkeditor (){
        document.querySelectorAll('.ckeditor').forEach((element) => {
            ClassicEditor
            .create(element)
            .catch(error => {
                console.error(error);
            });
        });
    }
}
