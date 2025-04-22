import { Controller } from "@hotwired/stimulus"

export default class extends Controller {
    connect(){
        const pathname = window.location.pathname.split("/")
        const categorieId = pathname[1]

        let allProduits = []
        fetch(`/categorie/${categorieId}/produits/list`)
            .then(res => res.json())
            .then(data => {
                allProduits = data.produits
                displayProducts(allProduits)
                attachFilterListeners()
                applyFilters()
        })
        console.log(allProduits)
    }
}