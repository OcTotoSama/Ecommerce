const chatBox = document.getElementById('chat-box');
const form = document.getElementById('chat-form');
const input = document.getElementById('chat-input');

/* =========================
   SCROLL INITIAL
========================= */

if (chatBox) {
    chatBox.scrollTop = chatBox.scrollHeight;
}

/* =========================
   ENVOI MESSAGE
========================= */

if (form) {

    form.addEventListener('submit', function (e) {

        e.preventDefault();

        const contenu = input.value.trim();

        if (!contenu) return;

        input.disabled = true;

        fetch(form.action, {

            method: 'POST',

            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Content-Type': 'application/x-www-form-urlencoded'
            },

            body: new URLSearchParams({
                contenu: contenu
            })

        })

        .then(response => response.json())

        .then(data => {

            if (data.success) {

                /* =========================
                   CREATION MESSAGE
                ========================= */

                const row = document.createElement('div');

                row.className = 'chat-row admin-row';

                row.innerHTML = `

                    <div class="chat-message admin-message">

                        <div class="chat-author">
                            Vous (admin)
                        </div>

                        <div class="chat-content">
                            ${data.contenu}
                        </div>

                        <div class="chat-date">
                            ${data.dateEnvoi}
                        </div>

                    </div>

                `;

                chatBox.appendChild(row);

                /* =========================
                   RESET
                ========================= */

                input.value = '';

                chatBox.scrollTop = chatBox.scrollHeight;
            }
        })

        .finally(() => {

            input.disabled = false;

            input.focus();
        });

    });

}