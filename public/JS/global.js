// Version : protections contre éléments absents et fallback ACCUEIL_URL

(function () {
    'use strict';

    // Si ACCUEIL_URL n'est pas défini globalement, on le reconstruit à partir de location
    // Cela évite les problèmes de Mixed Content si une valeur http est injectée.
    // Si tu fournis ACCUEIL_URL côté serveur, garde-le ; sinon ce fallback est sûr.
    const ACCUEIL_URL = (typeof window.ACCUEIL_URL !== 'undefined' && window.ACCUEIL_URL)
        ? (function (u) {
            try {
                // si l'URL commence par http:// et la page est en https, on force le protocole de la page
                const parsed = new URL(u, location.origin);
                if (location.protocol === 'https:' && parsed.protocol === 'http:') {
                    parsed.protocol = 'https:';
                }
                return parsed.toString();
            } catch (e) {
                return location.origin + '/';
            }
        })(window.ACCUEIL_URL)
        : (location.origin + '/');

    const emptyCartHTML = `
    <div id="empty-cart">
        <div class="back-panier">
        <h1 class="cart-title empty">
            Your cart is empty
        </h1>
            <div class="d-flex justify-content-center align-items-center" style="min-height:40vh;">
                <a href="${ACCUEIL_URL}" class="btn btn-dark px-4 py-3 fw-semibold">
                    So continue shopping here
                    <i class="bi bi-arrow-right-square"></i>
                </a>
            </div>
        </div>
    </div>
    `;

    // Helper : safe querySelectorAll -> retourne NodeList (vide si rien)
    function $all(selector, root = document) {
        return root.querySelectorAll(selector);
    }

    // Helper : safe querySelector -> retourne null si absent
    function $one(selector, root = document) {
        return root.querySelector(selector);
    }

    //  AJOUT AU PANIER (boutons présents sur plusieurs pages)
    (function initAddToCart() {
        const buttons = $all('.ajout-panier');
        if (!buttons || buttons.length === 0) return;

        buttons.forEach(button => {
            // protection : s'assurer que button existe (toujours vrai ici)
            button.addEventListener('click', function (e) {
                e.preventDefault();

                const auth = this.dataset.auth;

                if (auth !== 'true') {
                    // redirection vers login si non authentifié
                    window.location.href = this.getAttribute('href');
                    return;
                }

                const url = this.dataset.url;
                if (!url) return;

                fetch(url, {
                    method: 'POST',
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Mise à jour quantité sur la carte (page panier)
                        const container = this.closest('.ajout_panier');
                        if (container) {
                            const quantiteElement = container.querySelector('.in-panier');
                            if (quantiteElement) {
                                quantiteElement.innerText = data.quantite;
                            }
                        }

                        // Mise à jour compteur navbar
                        const cartCount = document.querySelector('#cart-count');
                        if (cartCount) {
                            cartCount.innerText = data.totalItems;
                        }

                        // Mise à jour résumé commande
                        const subtotal = document.querySelector('#subtotal');
                        const tva      = document.querySelector('#tva');
                        const total    = document.querySelector('#total');
                        if (subtotal && tva && total) {
                            subtotal.innerText = data.subtotal.toFixed(2) + ' €';
                            tva.innerText      = data.tva.toFixed(2)      + ' €';
                            total.innerText    = data.total.toFixed(2)    + ' €';
                        }

                        // Feedback "Ajouté" uniquement si ce n'est pas le bouton "+"
                        const originalContent = this.innerHTML;
                        if (originalContent.trim() !== '+') {
                            this.innerText = '✓ Ajouté';
                            setTimeout(() => {
                                this.innerHTML = originalContent;
                            }, 1000);
                        }
                    }
                })
                .catch(err => console.error('Erreur fetch (add to cart):', err));
            });
        });
    })();

    //  SUPPRESSION D'UN PRODUIT DU PANIER
    (function initRemoveFromCart() {
        const buttons = $all('.remove-panier');
        if (!buttons || buttons.length === 0) return;

        buttons.forEach(button => {
            button.addEventListener('click', function (e) {
                e.preventDefault();

                const url = this.getAttribute('href');
                if (!url) return;

                const container = this.closest('.card_anime');
                const quantiteElement = container ? container.querySelector('.in-panier') : null;

                fetch(url, {
                    method: 'POST',
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        if (data.removed && container) {
                            container.remove(); // produit supprimé du DOM
                        } else if (quantiteElement) {
                            quantiteElement.innerText = data.quantite;
                        }

                        // mise à jour résumé panier (si présents)
                        const subtotalEl = document.querySelector('#subtotal');
                        const tvaEl = document.querySelector('#tva');
                        const totalEl = document.querySelector('#total');
                        if (subtotalEl && tvaEl && totalEl) {
                            subtotalEl.innerText = data.subtotal.toFixed(2) + ' €';
                            tvaEl.innerText      = data.tva.toFixed(2)      + ' €';
                            totalEl.innerText    = data.total.toFixed(2)    + ' €';
                        }

                        // NAVBAR UPDATE
                        const cartCountEl = document.querySelector('#cart-count');
                        if (cartCountEl) cartCountEl.innerText = data.totalItems;

                        if (parseInt(data.totalItems) === 0) {
                            const cartContainer = document.querySelector('#cart-container');
                            if (cartContainer) cartContainer.innerHTML = emptyCartHTML;
                        }
                    }
                })
                .catch(err => console.error('Erreur fetch (remove from cart):', err));
            });
        });
    })();

    //  ANNIHILER (vider / supprimer)
    (function initAnnihiler() {
        const buttons = $all('.annihiler-panier');
        if (!buttons || buttons.length === 0) return;

        buttons.forEach(button => {
            button.addEventListener('click', function (e) {
                e.preventDefault();

                const url = this.getAttribute('href');
                if (!url) return;

                const container = this.closest('.card_anime');
                fetch(url, {
                    method: 'POST',
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        if (container) container.remove();

                        const subtotalEl = document.querySelector('#subtotal');
                        const tvaEl = document.querySelector('#tva');
                        const totalEl = document.querySelector('#total');
                        if (subtotalEl && tvaEl && totalEl) {
                            subtotalEl.innerText = data.subtotal.toFixed(2) + ' €';
                            tvaEl.innerText      = data.tva.toFixed(2)      + ' €';
                            totalEl.innerText    = data.total.toFixed(2)    + ' €';
                        }

                        const cartCountEl = document.querySelector('#cart-count');
                        if (cartCountEl) cartCountEl.innerText = data.totalItems;

                        if (parseInt(data.totalItems) === 0) {
                            const cartContainer = document.querySelector('#cart-container');
                            if (cartContainer) cartContainer.innerHTML = emptyCartHTML;
                        }
                    }
                })
                .catch(err => console.error('Erreur fetch (annihiler):', err));
            });
        });
    })();

    // Favoris (exécuté après DOMContentLoaded car dépend de dataset page)
    document.addEventListener('DOMContentLoaded', () => {
        const isFavorisPage = document.body && document.body.dataset && document.body.dataset.page === 'favoris';

        const favButtons = $all('.favori-toggle');
        if (favButtons && favButtons.length > 0) {
            favButtons.forEach(button => {
                button.addEventListener('click', function (e) {
                    const isAuthenticated = this.dataset.auth === 'true';

                    // Si pas connecté, laisser le comportement natif (redirect)
                    if (!isAuthenticated) {
                        return;
                    }

                    e.preventDefault();

                    const url = this.dataset.url;
                    if (!url) return;

                    const icon = this.querySelector('i');
                    const card = this.closest('.card_anime');

                    fetch(url, {
                        method: 'POST',
                        headers: { 'X-Requested-With': 'XMLHttpRequest' }
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (!icon) return;

                        if (data.isFavorite) {
                            icon.classList.remove('bi-heart');
                            icon.classList.add('bi-heart-fill');
                        } else {
                            icon.classList.remove('bi-heart-fill');
                            icon.classList.add('bi-heart');

                            if (isFavorisPage && card) {
                                card.remove();
                            }

                            if (isFavorisPage) {
                                const cards = document.querySelectorAll('.card_anime');
                                const title = document.getElementById('favoris-title');
                                const wrap = document.querySelector('.wrap');

                                if (!cards || cards.length === 0) {
                                    if (wrap) wrap.remove();
                                    if (title) title.textContent = "Pas de favoris";
                                }
                            }
                        }
                    })
                    .catch(error => console.error('Erreur fetch (favori):', error));
                });
            });
        }
    });

    // Recherche utilisateurs (protection si input absent)
    (function initUserSearch() {
        const searchInput = document.getElementById("userSearch");
        if (!searchInput) return;

        const rows = document.querySelectorAll(".user-row");
        searchInput.addEventListener("keyup", () => {
            const searchValue = searchInput.value.toLowerCase();
            rows.forEach(row => {
                const searchableCells = row.querySelectorAll(".searchable");
                let matchFound = false;
                searchableCells.forEach(cell => {
                    const text = (cell.textContent || '').toLowerCase();
                    if (text.includes(searchValue)) matchFound = true;
                });
                row.style.display = matchFound ? "" : "none";
            });
        });
    })();

    // Recherche produits (protection si input absent)
    (function initProductSearch() {
        const productSearch = document.getElementById("productSearch");
        if (!productSearch) return;

        const productRows = document.querySelectorAll(".product-row");
        productSearch.addEventListener("keyup", () => {
            const searchValue = productSearch.value.toLowerCase();
            productRows.forEach(row => {
                const searchableCells = row.querySelectorAll(".searchable");
                let matchFound = false;
                searchableCells.forEach(cell => {
                    const text = (cell.textContent || '').toLowerCase();
                    if (text.includes(searchValue)) matchFound = true;
                });
                row.style.display = matchFound ? "" : "none";
            });
        });
    })();

   
})();



let lastScrollY = window.scrollY;

const navbar = document.querySelector(".custom-navbar");

window.addEventListener("scroll", () => {

    const currentScrollY = window.scrollY;

    if (!navbar) return;

    // scroll vers le bas → cacher
    if (currentScrollY > lastScrollY && currentScrollY > 80) {
        navbar.classList.add("navbar-hidden");
    }

    // scroll vers le haut → montrer
    if (currentScrollY < lastScrollY) {
        navbar.classList.remove("navbar-hidden");
    }

    lastScrollY = currentScrollY;
});



/* =========================
   SCROLL TOP BUTTON
========================= */

const scrollTopBtn = document.getElementById("scrollTopBtn");

window.addEventListener("scroll", () => {

    if (window.scrollY > 400) {

        scrollTopBtn.classList.add("show");

    } else {

        scrollTopBtn.classList.remove("show");
    }
});

scrollTopBtn.addEventListener("click", () => {

    window.scrollTo({
        top: 0,
        behavior: "smooth"
    });
});