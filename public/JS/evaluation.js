document.addEventListener('DOMContentLoaded', () => {

    document.querySelectorAll('.etoiles').forEach(container => {

        const etoiles   = container.querySelectorAll('.etoile');
        const url       = container.dataset.url;
        const produitId = container.dataset.produitId;

        etoiles.forEach((etoile, index) => {

            etoile.addEventListener('mouseenter', () => {
                etoiles.forEach((e, i) => e.classList.toggle('hover', i <= index));
            });

            etoile.addEventListener('mouseleave', () => {
                etoiles.forEach(e => e.classList.remove('hover'));
            });

            etoile.addEventListener('click', () => {

                const note = etoile.dataset.note;

                fetch(url, {
                    method: 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: new URLSearchParams({
                        note: note
                    }),
                })

                .then(r => r.json())

                .then(data => {

                    if (data.success) {

                        etoiles.forEach((e, i) => {
                            e.classList.toggle('active', i < data.note);
                        });

                        const label = document.querySelector(`.note-label-${produitId}`);

                        if (label) {
                            label.innerText = `Votre note : ${data.note}/5`;
                        }

                        const moyenneEl = document.querySelector(`.moyenne-${produitId}`);

                        if (moyenneEl && data.moyenne) {
                            moyenneEl.innerText = `★ ${data.moyenne}/5`;
                        }
                    }
                });
            });
        });
    });

});