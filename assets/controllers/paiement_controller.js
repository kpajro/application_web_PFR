import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static values = {
        stripePublicKey: String,
        stripeUrl: String
    }

    connect() {
        this.stripe = Stripe(this.stripePublicKeyValue)

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