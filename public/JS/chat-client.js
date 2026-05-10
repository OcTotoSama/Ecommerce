const chatBox = document.getElementById('chat-box');
const form = document.getElementById('chat-form');
const input = document.getElementById('chat-input');

if (chatBox && form && input) {

    // scroll auto
    chatBox.scrollTop = chatBox.scrollHeight;

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
        .then(r => r.json())

        .then(data => {

            if (data.success) {

                const div = document.createElement('div');

                div.className = 'd-flex justify-content-end mb-3';

                div.innerHTML = `
                    <div class="message-bubble user-message">
                        
                        <div class="message-author">
                            Vous
                        </div>

                        <div class="message-content">
                            ${data.contenu}
                        </div>

                        <div class="message-date">
                            ${data.dateEnvoi}
                        </div>

                    </div>
                `;

                chatBox.appendChild(div);

                chatBox.scrollTop = chatBox.scrollHeight;

                input.value = '';
            }
        })

        .finally(() => {

            input.disabled = false;

            input.focus();

        });

    });

}