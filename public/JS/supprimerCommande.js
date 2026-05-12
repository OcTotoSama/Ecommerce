function supprimerCommande(btn) {
    const url = btn.dataset.url;
    const id = btn.closest('tr').dataset.commandeId;

    fetch(url, {
        method: 'DELETE',
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(res => res.json())
    .then(data => {
        if (!data.success) return;

        document.querySelectorAll(`tr[data-commande-id="${id}"]`).forEach(tr => tr.remove());
    });
}