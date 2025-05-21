import { Controller } from "@hotwired/stimulus";

export default class extends Controller {
    connect() {
        /* const form = document.getElementById('panier-form');
        form.addEventListener('submit', e => {
            e.preventDefault();
            this.sendForm(form);
        }) */
    }

    removeProduct(event) {
        event.preventDefault();
        const produit = event.currentTarget.parentElement.parentElement;
        const url = event.currentTarget.href;
        produit.remove();
        fetch(url)
            .then(response => {
                if(!response.ok) {
                    throw new Error("Erreur lors de la suppresion du produit dans le panier");
                }
                return response.text();
            }).then(text => {
                document.getElementById('panier-confirmation').innerText = text;
                setTimeout(() => {
                    document.getElementById('panier-confirmation').innerText = '';
                }, 3000);
            })
        ;
    }

    changeAmount(event) {
        const operation = event.currentTarget.dataset.operation;
        const parentElement = event.currentTarget.parentElement;
        const amount = parseInt(parentElement.dataset.amount);
        const id = parentElement.dataset.id;
        const input = parentElement.querySelector('.panier-input');
        const currentAmount = parseInt(input.value);
        const form = document.getElementById('panier-form');

        let newValue = 0;
        if (operation === 'plus') {
            newValue = currentAmount + amount;
        } else if (operation === 'minus') {
            newValue = currentAmount - amount;
            if (newValue < 1) return;
        }

        input.value = newValue;
        this.updatePrice(id, newValue);
        // form.submit();
    }

    updatePrice(id, amount) {
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
        prices.forEach(priceElement => {
            const priceV = priceElement.dataset.value;
            const ppAmount = priceElement.dataset.amount;

            totalPrice += priceV * ppAmount;
        })
        
        price.innerText = eur.format(newPrice);
        totalPriceElement.innerText = eur.format(totalPrice);
    } 

    sendForm(form) {
        const inputs = [...form.querySelectorAll('input')];
        
        let data = {};
        inputs.forEach(input => {
            if (input.dataset.id) {
                data[input.dataset.id] = input.value;
            }
        })

        const formData = new FormData(form)

        fetch('/panier/view', {
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