function recalculer() {
    let subtotal = 0;

    document.querySelectorAll('.item-check').forEach(cb => {
        if (cb.checked) {
            subtotal += parseFloat(cb.dataset.prix) * parseInt(cb.dataset.quantite);
        }
    });

    const tva = subtotal * 0.2;
    const total = subtotal + tva;

    const subtotalEl = document.getElementById('recap-subtotal');
    const tvaEl = document.getElementById('recap-tva');
    const totalEl = document.getElementById('recap-total');

    if (subtotalEl && tvaEl && totalEl) {
        subtotalEl.innerText = subtotal.toFixed(2) + ' €';
        tvaEl.innerText = tva.toFixed(2) + ' €';
        totalEl.innerText = total.toFixed(2) + ' €';
    }
}

document.addEventListener('DOMContentLoaded', () => {

    document.querySelectorAll('.item-check').forEach(cb => {
        cb.addEventListener('change', recalculer);
    });

    recalculer();
});