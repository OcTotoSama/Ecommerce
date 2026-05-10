document.addEventListener('DOMContentLoaded', () => {

    const actifs = document.getElementById('table-actifs');
    const inactifs = document.getElementById('table-inactifs');

    // ─────────────────────────────
    // DÉSACTIVATION
    // ─────────────────────────────
    document.addEventListener('click', async (e) => {

        const btn = e.target.closest('.btn-desactiver');
        if (!btn) return;

        e.preventDefault();

        const url = btn.dataset.url;

if (!url) {
    console.error("URL manquante sur bouton", btn);
    return;
}
        const row = btn.closest('tr');

        if (!url || !row) return;

        const res = await fetch(url, {
            method: 'POST',
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        });

        const data = await res.json();
        if (!data.success) return;

        const cells = row.querySelectorAll('td');

        const user = {
            id: data.id,
            name: cells[0]?.innerText.trim(),
            surname: cells[1]?.innerText.trim(),
            email: cells[2]?.innerText.trim(),
            date: cells[3]?.innerText.trim()
        };

        row.remove();

        const tr = document.createElement('tr');
        tr.className = 'user-row text-muted';
        tr.dataset.id = user.id;

        tr.innerHTML = `
            <td>${user.name}</td>
            <td>${user.surname}</td>
            <td>${user.email}</td>
            <td>${user.date}</td>
            <td class="text-center">
                <a href="javascript:void(0);"
                   class="action-icon text-success btn-reactiver"
                   data-url="{{ path('app_desactiver_utilisateur', {'id': element.id}) }}">
                    <i class="bi bi-arrow-counterclockwise"></i>
                </a>
            </td>
        `;

        inactifs.appendChild(tr);
    });

    // ─────────────────────────────
    // RÉACTIVATION
    // ─────────────────────────────
    document.addEventListener('click', async (e) => {

        const btn = e.target.closest('.btn-reactiver');
        if (!btn) return;

        e.preventDefault();

        const url = btn.dataset.url;

if (!url) {
    console.error("URL manquante sur bouton", btn);
    return;
}
        const row = btn.closest('tr');

        if (!url || !row) return;

        const res = await fetch(url, {
            method: 'GET',
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        });

        const data = await res.json();
        if (!data.success) return;

        row.remove();

        const tr = document.createElement('tr');
        tr.className = 'user-row';
        tr.dataset.id = data.id;

        const role = data.isAdmin
            ? `<span class="role-badge role-admin">
                    <i class="bi bi-shield-lock-fill"></i> Admin
               </span>`
            : `<span class="role-badge role-user">
                    <i class="bi bi-person-fill"></i> Utilisateur
               </span>`;

        tr.innerHTML = `
            <td>${data.name}</td>
            <td>${data.surname}</td>
            <td>${data.email}</td>
            <td>${data.dateInscription}</td>

            <td class="text-center">${role}</td>

            <td class="text-center">
                ${data.isAdmin
                    ? `<i class="bi bi-lock-fill text-muted"></i>`
                    : `<a href="/modifier-utilisateur/${data.id}" class="action-icon">
                            <i class="bi bi-pen-fill"></i>
                       </a>`
                }
            </td>

            <td class="text-center">
                ${data.isAdmin
                    ? `<i class="bi bi-lock-fill text-muted"></i>`
                    : `<a href="javascript:void(0);"
                         class="action-icon text-warning btn-desactiver"
                         data-url="{{ path('app_reactiver_utilisateur', {'id': element.id}) }}">
                            <i class="bi bi-person-dash-fill"></i>
                       </a>`
                }
            </td>
        `;

        actifs.appendChild(tr);
    });

});