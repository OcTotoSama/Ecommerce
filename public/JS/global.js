const emptyCartHTML = `
    <div id="empty-cart">
        <div class="back-panier">
            <h1 class="text-center box_blur_panier">Your cart is empty</h1>
            <div class="d-flex justify-content-center align-items-center" style="min-height:40vh;">
                <a href="${ACCUEIL_URL}" class="btn btn-dark px-4 py-3 fw-semibold">
                    So continue shopping here
                    <i class="bi bi-arrow-right-square"></i>
                </a>
            </div>
        </div>
    </div>
`;


/* ➕ AJOUT */
document.querySelectorAll('.ajout-panier').forEach(button => {

    button.addEventListener('click', function (e) {

        const isAuthenticated = this.dataset.auth === 'true';

        // ❌ pas connecté → laisser le lien fonctionner (redirect login)
        if (!isAuthenticated) {
            return;
        }

        // ✅ connecté → AJAX
        e.preventDefault();

        const url = this.dataset.url;

        const container = this.closest('.ajout_panier');
        const quantiteElement = container ? container.querySelector('.in-panier') : null;

        fetch(url, {
            method: 'POST',
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(response => response.json())
        .then(data => {

            if (data.success) {

                if (quantiteElement) {
                    quantiteElement.innerText = data.quantite;
                }

                const subtotal = document.querySelector('#subtotal');
                const tva = document.querySelector('#tva');
                const total = document.querySelector('#total');

                if (subtotal && tva && total) {
                    subtotal.innerText = data.subtotal.toFixed(2) + ' €';
                    tva.innerText = data.tva.toFixed(2) + ' €';
                    total.innerText = data.total.toFixed(2) + ' €';
                }

                const cartCount = document.querySelector('#cart-count');
                if (cartCount) {
                    cartCount.innerText = data.totalItems;
                }

                const originalContent = this.innerHTML;

                if (originalContent !== '+') {
                    this.innerText = "Ajouté";

                    setTimeout(() => {
                        this.innerHTML = originalContent;
                    }, 1000);
                }
            }
        });
    });

});


/* ➖ SUPPRESSION */
document.querySelectorAll('.remove-panier').forEach(button => {
    button.addEventListener('click', function (e) {

        e.preventDefault();

        const url = this.getAttribute('href');
        const container = this.closest('.card_anime');
        const quantiteElement = container.querySelector('.in-panier');

        fetch(url, {
            method: 'POST',
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
            .then(response => response.json())

            .then(data => {

                if (data.success) {

                    if (data.removed) {
                        container.remove(); // produit supprimé du DOM
                    } else {
                        quantiteElement.innerText = data.quantite;
                    }

                    // ✔ mise à jour résumé panier
                    document.querySelector('#subtotal').innerText = data.subtotal.toFixed(2) + ' €';
                    document.querySelector('#tva').innerText = data.tva.toFixed(2) + ' €';
                    document.querySelector('#total').innerText = data.total.toFixed(2) + ' €';

                    // 🧠 NAVBAR UPDATE
                    document.querySelector('#cart-count').innerText = data.totalItems;

                    if (parseInt(data.totalItems) === 0) {
                        const container = document.querySelector('#cart-container');
                        container.innerHTML = emptyCartHTML;
                    }
                }
            });

    });
});

/* ➖➖ annihiler */
document.querySelectorAll('.annihiler-panier').forEach(button => {
    button.addEventListener('click', function (e) {

        e.preventDefault();

        const url = this.getAttribute('href');
        const container = this.closest('.card_anime'); // ou ton wrapper produit
        const quantiteElement = container.querySelector('.in-panier');

        fetch(url, {
            method: 'POST',
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
            .then(response => response.json())
            .then(data => {

                if (data.success) {

                    // 🔥 suppression directe de la carte
                    container.remove();

                    // 🔥 update résumé commande
                    document.querySelector('#subtotal').innerText = data.subtotal.toFixed(2) + ' €';
                    document.querySelector('#tva').innerText = data.tva.toFixed(2) + ' €';
                    document.querySelector('#total').innerText = data.total.toFixed(2) + ' €';

                    // 🧠 NAVBAR UPDATE
                    document.querySelector('#cart-count').innerText = data.totalItems;

                    if (parseInt(data.totalItems) === 0) {
                        const container = document.querySelector('#cart-container');
                        container.innerHTML = emptyCartHTML;
                    }
                }

            });
    });
});



document.addEventListener('DOMContentLoaded', () => {

    const isFavorisPage = document.body.dataset.page === 'favoris';

    document.querySelectorAll('.favori-toggle').forEach(button => {

        button.addEventListener('click', function (e) {
            

            const isAuthenticated = this.dataset.auth === 'true';
        
            // ❌ Si PAS connecté → on laisse Symfony gérer (redirect login)
            if (!isAuthenticated) {
                return;
            }
        
            // ✅ Sinon AJAX
                
            e.preventDefault();

            const url = this.dataset.url;
            const icon = this.querySelector('i');
            const card = this.closest('.card_anime');

            fetch(url, {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
                .then(response => response.json())
                .then(data => {

                    // 🔁 Mise à jour icône dans tous les cas
                    if (data.isFavorite) {
                        icon.classList.remove('bi-heart');
                        icon.classList.add('bi-heart-fill');
                    } else {
                        icon.classList.remove('bi-heart-fill');
                        icon.classList.add('bi-heart');

                        // ❌ suppression UNIQUEMENT si page favoris
                        if (isFavorisPage && card) {
                            card.remove();
                        }

                        // 🧠 gestion si plus de favoris (UNIQUEMENT page favoris)
                        if (isFavorisPage) {
                            const cards = document.querySelectorAll('.card_anime');
                            const title = document.getElementById('favoris-title');
                            const wrap = document.querySelector('.wrap');

                            if (cards.length === 0) {
                                if (wrap) wrap.remove();
                                if (title) title.textContent = "Pas de favoris";
                            }
                        }
                    }

                })
                .catch(error => console.error(error));
        });

    });

});

