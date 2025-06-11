import { Controller } from "@hotwired/stimulus";

/**
 * Controller js dédié à la gestion de l'interface des paniers
 */
export default class extends Controller {
    connect() {
        
    }

    /**
     * Fonction d'événement pour supprimer un produit du panier
     * @param {*} event 
     */
    removeProduct(event) {
        event.preventDefault();
        const produit = event.currentTarget.parentElement.parentElement;        // on récupère l'élément entier du produit
        const url = event.currentTarget.href;       // lien de la route de suppression renseignée sur l'élément
        produit.remove();
        fetch(url)      // fetch sur la route de suppression
            .then(response => {
                if(!response.ok) {
                    throw new Error("Erreur lors de la suppresion du produit dans le panier");
                }
                return response.text();
            }).then(text => {       // on met un petit message de confirmation quand ça a bien fonctionné
                document.getElementById('panier-confirmation').innerText = text;
                setTimeout(() => {
                    document.getElementById('panier-confirmation').innerText = '';
                }, 3000);
            })
        ;
    }

    /**
     * Fonction pour modifier la quantité d'un produit dans le panier
     * @param {*} event 
     * @returns 
     */
    changeAmount(event) {
        const operation = event.currentTarget.dataset.operation;        // le type d'opération voulu (+ ou -)
        const parentElement = event.currentTarget.parentElement;
        const amount = parseInt(parentElement.dataset.amount);      // quantité du produit (souvent 1 mais c'est au cas où on est sur un produit en bulk)
        const id = parentElement.dataset.id;
        const input = parentElement.querySelector('.panier-input');
        const currentAmount = parseInt(input.value);
        const form = document.forms['panier-form'];

        let newValue = 0;
        // on créé un nouvelle valeur, selon le type d'opération on ajoute ou retire la quantité voulue à la quantité qu'on a déjà
        if (operation === 'plus') {
            newValue = currentAmount + amount;
        } else if (operation === 'minus') {
            newValue = currentAmount - amount;
            if (newValue < 1) return; // on ne peut pas aller en dessous de 1
        }

        input.value = newValue;
        this.updatePrice(id, newValue); // modification de quantité, on update le prix 
        this.sendForm(form);    // envoi du formulaire pour sauvegarder les modifications dans la bdd
    }

    /**
    * Fonction pour update le prix d'un élément du panier et le prix total dès qu'il y a un changement
    * @param {*} id 
    * @param {*} amount 
    */
    updatePrice(id, amount) {
        // récupération de tous les éléments de prix qui sont à modifier (prix du produit + prix total du panier)
        const price = document.getElementById(id).querySelector('.panier-price');
        const prices = [...document.querySelectorAll('.panier-price')];
        const priceValue = parseFloat(price.dataset.value);
        const totalPriceElement = document.getElementById('panier-total-price');
        const eur = new Intl.NumberFormat('fr', {
            style: 'currency',
            currency: 'EUR',
        });

        let totalPrice = 0;
        let newPrice = priceValue * amount;

        price.dataset.amount = amount;
        // on recalcule le prix total
        prices.forEach(priceElement => {
            const priceV = priceElement.dataset.value;
            const ppAmount = priceElement.dataset.amount;

            totalPrice += priceV * ppAmount;
        })
        
        price.innerText = eur.format(newPrice);
        totalPriceElement.innerText = eur.format(totalPrice);
    } 

    /**
     * Fonction pour envoyer le formulaire au backend
     * @param {*} form 
     */
    sendForm(form) {
        const inputs = [...form.querySelectorAll('input')];
        const data = {};
        const formData = new FormData(form);
        inputs.forEach(input => {
            if (input.dataset.id) {
                formData[input.dataset.id] = input.value;
                data[input.dataset.id] = input.value;
            }
        })

        fetch(form.action, {
            method: 'POST',
            body: data
        })
            .then(res => {
                if (!res.ok) {
                    console.log(res);
                    throw new Error("Erreur lors de l'envoi du formulaire.");
                }
            })
        ;
    }
}