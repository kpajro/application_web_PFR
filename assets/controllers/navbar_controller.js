import { Controller } from "@hotwired/stimulus";

export default class extends Controller {
    connect() {
        document.addEventListener('click', e => {
            const menu = document.getElementById('mobile-links')
            if (
                menu !== null 
                && typeof e.composedPath === 'function' 
                &&  !e.composedPath().includes(menu) 
                && !e.composedPath().includes(document.getElementById('mobile-menu-btn'))
            ) {
                this.closeMobileMenu(e, menu);
            }
        })
    }

    openMobileMenu(e) {
        e.preventDefault();
        if (document.getElementById('mobile-links') !== null) {
            return;
        }
        const links = document.getElementById('links');
        const linksBlock = document.createElement('div');
        linksBlock.id = "mobile-links";
        linksBlock.classList.add('flex', 'flex-col', 'gap-2', 'mt-4');
        const navBar = document.getElementById('navbar');
        linksBlock.innerHTML = links.innerHTML;
        navBar.appendChild(linksBlock);

        e.currentTarget.addEventListener('click', e => {
            this.closeMobileMenu(e, linksBlock);
        });
    }

    closeMobileMenu(e, menu) {
        e.preventDefault();
        if (menu) {
            menu.classList.add('hidden');
            menu.remove();
        }
    }
}