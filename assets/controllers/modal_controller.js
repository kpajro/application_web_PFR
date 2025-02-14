import { Controller } from "@hotwired/stimulus";

export default class extends Controller {
    connect() {

    }

    open(event) {
        event.preventDefault();
        const url = event.currentTarget.dataset.modalUrl;
        console.log(url);
        const modal = document.getElementById("modal");

        modal.classList.remove("hidden"); // on affiche la modale

        fetch(url, { method: "POST" }) //requête pour récupérer le contenu voulu
            .then((response) => {
                if (!response.ok) {
                    throw new Error("Erreur lors du chargement du contenu");
                }
                return response.text();
            })
            .then(html => {
                this.element.querySelector("#modal-content").innerHTML = html; //le contenu chargé est injecté dans la modale
                this.closeModalHandler(modal); //on charge les boutons pour fermer la modale
            })
            .catch(error => {
                console.error(error);
                this.element.querySelector("#modal-content").innerHTML =
                    "<p class='text-red-500'>Impossible de charger le contenu demandé.</p>"
                ;
            }
        );
    }

    closeModalHandler(modal) {
        const closeButtons = this.element.querySelectorAll("#close-modal");

        closeButtons.forEach((button) => {
            button.addEventListener("click", (event) => {
                event.preventDefault();

                // Masquer la modale
                modal.classList.add("hidden");

                // Nettoyer le contenu
                modal.querySelector("#modal-content").innerHTML = "";
            });
        });
    }
}