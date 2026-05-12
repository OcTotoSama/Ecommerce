function supprimerCommande(btn) {

    const url = btn.dataset.url;

    const id =
        btn.closest('tr').dataset.commandeId;

    const rows =
        document.querySelectorAll(
            `tr[data-commande-id="${id}"]`
        );

    fetch(url, {
        method: 'DELETE',
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })

    .then(res => res.json())

    .then(data => {

        if (!data.success) return;

        /* =========================
           ANIMATION
        ========================= */

        rows.forEach(tr => {

            tr.style.transition =
                'opacity 0.35s ease, transform 0.35s ease';

            tr.style.opacity = '0';

            tr.style.transform =
                'translateX(-40px)';
        });

        setTimeout(() => {

            /* =========================
               REMOVE
            ========================= */

            rows.forEach(tr => tr.remove());

            /* =========================
               UPDATE STATS
            ========================= */

            document.getElementById(
                'stat-nb-commandes'
            ).textContent = data.nbCommandes;

            document.getElementById(
                'stat-revenu'
            ).textContent = data.revenu + ' €';

        }, 350);

    });

}