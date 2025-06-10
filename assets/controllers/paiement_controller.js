import { Controller } from '@hotwired/stimulus';

/**
 * Controller js dédié à l'initialisation et la gestion des paiements via stripe
 */
export default class extends Controller {
    static values = {
        stripeUrl: String
    }

    /**
     * Fonction lancée au chargement qui initialise et fetch stripe
     */
    connect() {
        const stripePKey = "pk_test_51RSvQkRVumHN60ooKlCL6qUPaVblzy3dtuAP3XwdF8LChY4G56VLJKpi526WBpi3VUEy0XcJifynKetmnul5Us7100AS1ThEJH"        // clé publique de stripe
        this.stripe = Stripe(stripePKey)

        this.element.addEventListener("click", () => {
            fetch(this.stripeUrlValue, {
                method: "POST",
                headers: {
                    "Content-Type": "application/json"
                },
            })
            .then(resp => resp.json())
            .then(session => {
                return this.stripe.redirectToCheckout({ sessionId: session.id})
            })
            .then(result => {
                if (result.error) {
                    alert(result.error.message)
                }
            })
            .catch(error => console.error("Erreur Stripe :", error))
        })
    }
}