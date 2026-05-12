function changerRole(btn) {
    fetch(btn.dataset.url, {
        method: 'GET',
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(res => res.json())
    .then(data => {
        if (!data.success) return;

        if (data.newRole === 'admin') {
            btn.className = btn.className.replace('role-user', 'role-admin');
            btn.innerHTML = '<i class="bi bi-shield-lock-fill"></i> Admin';
        } else {
            btn.className = btn.className.replace('role-admin', 'role-user');
            btn.innerHTML = '<i class="bi bi-person-fill"></i> User';
        }
    });
}