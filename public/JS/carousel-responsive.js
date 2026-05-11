// public/js/carousel-responsive.js
// Responsive carousel : mesure proprement la largeur réelle des cartes fixes
// et regroupe les cartes en slides sans modifier la taille des cartes.
(function () {
    'use strict';

    function clamp(v, a, b) {
        return Math.max(a, Math.min(b, v));
    }

    function initCarousel(carouselEl) {
        if (!carouselEl) return;

        // Récupère les HTML des cartes source (une seule fois)
        if (!carouselEl._cardsHtml) {
            const source = carouselEl.querySelector('[data-carousel-items]');
            if (!source) return;
            const items = Array.from(source.querySelectorAll('[data-carousel-item]'));
            carouselEl._cardsHtml = items.map(i => i.outerHTML);
        }

        // Mesure propre d'une carte fixe sans impacter le layout visible
        function measureCardWidth() {
            // Cherche un élément existant dans le DOM
            const existing = carouselEl.querySelector('[data-carousel-item] .carousel-card-wrapper');
            if (existing) {
                const rect = existing.getBoundingClientRect();
                return Math.max(1, Math.round(rect.width));
            }

            // Sinon, crée un conteneur hors écran pour mesurer
            const temp = document.createElement('div');
            temp.setAttribute('data-temp-measure', '1');
            temp.style.position = 'absolute';
            temp.style.left = '-9999px';
            temp.style.top = '-9999px';
            temp.style.visibility = 'hidden';
            temp.style.pointerEvents = 'none';
            temp.innerHTML = carouselEl._cardsHtml[0] || '';
            document.body.appendChild(temp);
            const sample = temp.querySelector('.carousel-card-wrapper');
            let w = 220;
            if (sample) {
                const rect = sample.getBoundingClientRect();
                w = Math.max(1, Math.round(rect.width));
            }
            document.body.removeChild(temp);
            return w;
        }

        function getNumericStyleProperty(el, prop, fallback = 0) {
            if (!el) return fallback;
            const val = getComputedStyle(el)[prop];
            return val ? parseFloat(val.replace('px', '')) || fallback : fallback;
        }

        function rebuild() {
            const inner = carouselEl.querySelector('.carousel-inner');
            if (!inner) return;
            inner.innerHTML = '';

            const cardsHtml = carouselEl._cardsHtml;
            if (!cardsHtml || cardsHtml.length === 0) return;

            // Mesure la largeur réelle d'une carte (fixe via CSS)
            const cardWidth = measureCardWidth();

            // Récupérer gap depuis le CSS (si possible)
            // On prend la valeur du gap sur une grille existante sinon fallback 20
            const gridRef = carouselEl.querySelector('.carousel-grid');
            const gap = getNumericStyleProperty(gridRef, 'gap', 20);

            // largeur disponible pour le carousel
            const containerWidth = carouselEl.clientWidth || carouselEl.getBoundingClientRect().width || window.innerWidth;

            // Calcul : combien de cartes tiennent en largeur en tenant compte du gap
            let itemsPerSlide = Math.floor((containerWidth + gap) / (cardWidth + gap));
            itemsPerSlide = clamp(itemsPerSlide, 1, 4);

            // Regroupement des cartes en slides
            for (let i = 0; i < cardsHtml.length; i += itemsPerSlide) {
                const slice = cardsHtml.slice(i, i + itemsPerSlide);
                const itemDiv = document.createElement('div');
                itemDiv.className = 'carousel-item';

                const grid = document.createElement('div');
                grid.className = 'carousel-grid';

                // Forcer le template de colonnes à la largeur réelle des cartes (fixes)
                // On utilise des colonnes fixes en px pour éviter le décalage/alignement à gauche
                const cols = slice.length;
                grid.style.gridTemplateColumns = `repeat(${cols}, ${cardWidth}px)`;
                grid.style.gap = `${gap}px`;
                grid.style.justifyContent = 'center';
                grid.style.justifyItems = 'center';

                grid.innerHTML = slice.join('');
                itemDiv.appendChild(grid);
                inner.appendChild(itemDiv);
            }

            // Marquer le premier slide actif
            const first = inner.querySelector('.carousel-item');
            if (first) {
                inner.querySelectorAll('.carousel-item').forEach((el) => el.classList.remove('active'));
                first.classList.add('active');
            }

            // Masquer les contrôles si une seule slide
            const slidesCount = inner.querySelectorAll('.carousel-item').length;
            const prev = carouselEl.querySelector('.carousel-control-prev');
            const next = carouselEl.querySelector('.carousel-control-next');
            if (prev && next) {
                if (slidesCount <= 1) {
                    prev.style.display = 'none';
                    next.style.display = 'none';
                } else {
                    prev.style.display = '';
                    next.style.display = '';
                }
            }
        }

        // Expose rebuild pour debug si besoin
        carouselEl._rebuild = rebuild;

        // Initial build
        rebuild();

        // Rebuild on resize with debounce
        let resizeTimer = null;
        function onResize() {
            if (resizeTimer) clearTimeout(resizeTimer);
            resizeTimer = setTimeout(() => {
                rebuild();
                resizeTimer = null;
            }, 120);
        }

        window.addEventListener('resize', onResize);
    }

    // Initialiser tous les carousels présents
    function initAll() {
        const carousels = document.querySelectorAll('.custom-carousel');
        carousels.forEach(initCarousel);
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initAll);
    } else {
        initAll();
    }

})();
