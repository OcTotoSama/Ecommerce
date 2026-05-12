function supprimerEvaluation(btn) {
    const url = btn.dataset.url;

    fetch(url, {
        method: 'DELETE',
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(res => res.json())
    .then(data => {
        if (!data.success) return;
        btn.closest('tr').remove();
        document.getElementById('stat-nb-evaluations').textContent = data.nbEvaluations;
    });
}