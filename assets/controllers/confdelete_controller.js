import { Controller } from "@hotwired/stimulus";

export default class extends Controller {
    connect() {
        const deleteBtn = document.getElementById('delete-btn');
        const confBox = document.getElementById('delete-conf');
        const cancelBtn = document.getElementById('cancel-delete');

        deleteBtn.addEventListener('click', e => {
            e.preventDefault();
            confBox.classList.remove('hidden');
            confBox.classList.remove('scale-y-0');

            cancelBtn.addEventListener('click', e => {
                e.preventDefault();
                confBox.classList.add('scale-y-0');
                // setTimeout(() => {
                //     confBox.classList.add('hidden');
                // }, 150);
            })
        })
    }
}