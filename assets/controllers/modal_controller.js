import { Controller } from "@hotwired/stimulus";

export default class extends Controller {
    connect() {
        
    }

    open(event) {
        event.preventDefault();
        const url = event.currentTarget.dataset.modalUrl;
        const size = event.currentTarget.dataset.modalSize;
        const modal = document.getElementById("modal");
        const box = document.getElementById("modal-box");
        modal.classList.remove("hidden"); // on affiche la modale
        switch (size) {
            case 'sm':
                box.classList.add('max-w-3xl');
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

        fetch(url, { method: "POST" }) //requête pour récupérer le contenu voulu
            .then((response) => {
                if (!response.ok) {
                    throw new Error("Erreur lors du chargement du contenu");
                }
                return response.text();
            })
            .then(html => {
                    this.element.querySelector("#modal-content").innerHTML = html; //le contenu chargé est injecté dans la modale
                    this.closeModalHandler(modal, box); //on charge les boutons pour fermer la modale
                    
                    modal.classList.remove("opacity-0");
                    box.classList.remove("translate-y-[100vh]");
                })
            .catch(error => {
                console.error(error);
                this.element.querySelector("#modal-content").innerHTML =
                    "<p class='text-red-500'>Impossible de charger le contenu demandé.</p>"
                    ;
                    
                box.classList.remove("translate-y-[100vh]");
            })
        ;
    }

    close(event, modal, box) {
        event.preventDefault();
        // Masquer la modale
        box.classList.add("translate-y-[100vh]");
        modal.classList.add('opacity-0');
        console.log(event)

        setTimeout(() => {
            modal.classList.add("hidden");
            // Nettoyer le contenu
            modal.querySelector("#modal-content").innerHTML = "";
            this.removeSize(box);
        }, 200);
    }

    closeModalHandler(modal, box) {
        const closeButtons = this.element.querySelectorAll(".close-modal");
        closeButtons.forEach((button) => {
            button.addEventListener("click", (event) => {
                this.close(event, modal, box);
            });
        });
        document.addEventListener('click', e => {
            if (typeof e.composedPath === 'function' &&  !e.composedPath().includes(box) && !box.classList.contains('translate-y-[100vh]')) {
                this.close(e, modal, box);
            }
        })
    }

    removeSize(box) {
        const classes = [...box.classList];
        const sizeClass = classes.find(cls => cls.startsWith('max-w-'));
        box.classList.remove(sizeClass);
    }

    updateNavButtons () {
        const contentDiv = document.getElementById('content');
        const contentAmount = contentDiv.dataset.contentAmount;
        const currentPosition = contentDiv.dataset.currentContent;
        const leftBtn = document.getElementById('left');
        const rightBtn = document.getElementById('right');

        if (contentAmount === currentPosition) {
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

    changeWindow(event) {
        if (!event.currentTarget.classList.contains('activated')) {
            return;
        }

        const currentPosition = document.getElementById('content').dataset.currentContent;
        const contentAmount = document.getElementById('content').dataset.contentAmount;
        const currentContent = document.getElementById(currentPosition);
        
        const direction = event.currentTarget.id;
        const next = parseInt(currentPosition) + 1 <= parseInt(contentAmount) ? parseInt(currentPosition) + 1 : 1;
        const previous = parseInt(currentPosition) - 1 >= 1 ? parseInt(currentPosition) - 1 : parseInt(contentAmount);
        
        if (direction === 'left') {
            const newContent = document.getElementById(previous);
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
            const newContent = document.getElementById(next);
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
}