function supprimerMaCommande(btn) {

    const url = btn.dataset.url;

    const row = btn.closest('tr');

    fetch(url, {

        method: 'DELETE',

        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }

    })

    .then(res => res.json())

    .then(data => {

        if (!data.success) {
            return;
        }

        /* animation */

        row.classList.add('removing');

        setTimeout(() => {

            row.remove();

            const tbody = document.querySelector(
                '.mes-commandes-table tbody'
            );

            if (
                tbody &&
                tbody.querySelectorAll('tr').length === 0
            ) {
                window.location.reload();
            }

        }, 350);

    })

    .catch(error => {

        console.error(error);

        alert(
            "Erreur lors de la suppression."
        );
    });
}