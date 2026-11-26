(function (window, document) {
    'use strict';

    document.addEventListener('DOMContentLoaded', init);

    const formBox = document.getElementById('form-box');
    let formVisible = false;
    let mainInput = null;

    const showForm = (messageId, message, note) => {
        if (formVisible) {
            mainInput.focus();
            return;
        }
        formVisible = true;
        // @todo В настройки
        const MIN_LENGTH = 10;
        const MAX_LENGTH = 5000;

        const form = document.createElement('form');
        form.classList.add('form');
        form.method = 'post';
        form.action = '/message/add';
        const messages = JokeForum.createEl('div', ['message', 'message_error']);
        messages.style.display = 'none';

        const text = JokeForum.createTextAreaInput('message', 'Введите текст сообщения', [], [], {});
        const textarea = text.querySelector('textarea');
        textarea.value = message;
        textarea.addEventListener('input', () => {
            if (textarea.value.length > 0) {
                if (messages.style.display === 'none') {
                    messages.style.display = 'block';
                }
            }
        });
        mainInput = textarea;

        const noteBox = JokeForum.createTextAreaInput('note', 'Введите примечание', [], [], {});
        const noteTextarea = noteBox.querySelector('textarea');
        noteTextarea.value = note;
        const buttons = JokeForum.createEl('div', ['forum-toolbar']);
        const btn = document.createElement('button');
        btn.disabled = true;
        const messageIdInput = JokeForum.createHidden('messageId', messageId);
        const csrf = JokeForum.createHidden('csrf_token', JokeForum.getCsrfToken());

        btn.textContent = 'Отправить';
        btn.className = 'btn btn_primary';
        form.appendChild(messages);
        form.appendChild(text);
        form.appendChild(noteBox);
        form.appendChild(messageIdInput);
        form.appendChild(csrf);
        buttons.appendChild(btn);
        form.appendChild(buttons);
        formBox.appendChild(form);
        mainInput.focus();

        function validateInput() {
            const length = textarea.value.length;

            messages.textContent = '';
            messages.style.display = 'none';
            if (length === 0) {
                btn.disabled = true;
                return;
            }
            if (length < MIN_LENGTH) {
                btn.disabled = true;
                return;
            }
            if (length > MAX_LENGTH) {
                btn.disabled = true;
                return;
            }
            btn.disabled = false;
        }

        textarea.addEventListener('input', validateInput);

        const showError = (error) => {
            messages.textContent = error;
            messages.style.display = 'block';
        }

        form.addEventListener('submit', async function (event) {
            event.preventDefault();
            event.stopPropagation();

            const length = textarea.value.length;
            if (length < MIN_LENGTH || length > MAX_LENGTH) {
                showError(
                    length < MIN_LENGTH
                        ? `Сообщение слишком короткое. Минимум ${MIN_LENGTH} символов.`
                        : `Сообщение слишком длинное. Максимум ${MAX_LENGTH} символов.`
                );
                return;
            }
            try {
                const response = await fetch('/message', {
                    method: 'PUT',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-Token': JokeForum.getCsrfToken(),
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        message: textarea.value,
                        messageId: messageId,
                        note: noteTextarea.value
                    }),
                });

                if (!response.ok && response.status === 403) {
                    showError('Вы не авторизованы или у вас нет прав на отправку сообщения');
                    textarea.focus();
                    return;
                }

                const data = await response.json();
                if (data.success) {
                    const url = new URL(window.location.href);
                    url.hash = data.id;
                    window.location.href = url.toString();
                    location.reload();
                } else {
                    showError(data.message);
                }

            } catch (error) {
                showError('Произошла ошибка при отправке сообщения');
            }
        });
    }
    const showBanForm = (id) => {
        if (formVisible) {
            document.querySelector('textarea[name=message]').focus();
            return;
        }
        formVisible = true;
        const BAN_DAYS = 2;

        const banDate = new Date();
        banDate.setDate(banDate.getDate() + BAN_DAYS);

        // Форматируем в YYYY-MM-DDTHH:mm (локальное время)
        const year = banDate.getFullYear();
        const month = String(banDate.getMonth() + 1).padStart(2, '0');
        const day = String(banDate.getDate()).padStart(2, '0');
        const hours = String(banDate.getHours()).padStart(2, '0');
        const minutes = String(banDate.getMinutes()).padStart(2, '0');

        const formattedDate = `${year}-${month}-${day}`;


        const form = document.createElement('form');
        form.classList.add('form');
        form.method = 'post';
        form.action = '/ban';
        const messages = JokeForum.createEl('div', ['message', 'message_error']);
        messages.style.display = 'none';

        const date = JokeForum.createTextInput('date', 'date', 'Введите дату окончания блокировки', [], [], {
            attributes: {
                required: true,
                value: formattedDate,
            }
        });
        const dateInput = date.querySelector('input');
        mainInput = dateInput;
        const buttons = JokeForum.createEl('div', ['forum-toolbar']);
        const btn = document.createElement('button');

        btn.textContent = 'Отправить';
        btn.className = 'btn btn_primary';
        form.appendChild(messages);
        form.appendChild(dateInput);
        buttons.appendChild(btn);
        form.appendChild(buttons);
        formBox.appendChild(form);
        mainInput.focus();

        const showError = (error) => {
            messages.textContent = error;
            messages.style.display = 'block';
        }

        form.addEventListener('submit', async function (event) {
            event.preventDefault();
            event.stopPropagation();

            try {
                const response = await fetch('/ban/' + id, {
                    method: 'PUT',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-Token': JokeForum.getCsrfToken(),
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        date: dateInput.value+`T${hours}:${minutes}:00`,
                    }),
                });

                if (!response.ok && response.status === 403) {
                    showError('Вы не авторизованы или у вас нет прав');
                    textarea.focus();
                    return;
                }

                const data = await response.json();
                if (data.success) {
                    location.reload();
                } else {
                    showError(data.message);
                }

            } catch (error) {
                showError('Произошла ошибка');
            }
        });
    }
// @todo проверки перенести на customVerification
    const deleteMessage = (messageId) => {
        try {
            fetch('/message/' + messageId, {
                method: 'DELETE',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-Token': JokeForum.getCsrfToken(),
                    'Content-Type': 'application/json',
                }
            }).then(response => response.json()).then(data => {
                if (data.success) {
                    window.location.reload();
                } else {
                    alert('Ошибка удаления сообщения')
                }
            })
        } catch (error) {
            console.error('error', error)
        }
    }
    const unbanUser = (id) => {
        try {
            fetch('/ban/' + id, {
                method: 'DELETE',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-Token': JokeForum.getCsrfToken(),
                    'Content-Type': 'application/json',
                }
            }).then(response => response.json()).then(data => {
                if (data.success) {
                    window.location.reload();
                } else {
                    alert('Ошибка удаления сообщения')
                }
            })
        } catch (error) {
            console.error('error', error)
        }
    }

    function init() {
        const buttonsEdit = document.querySelectorAll('.jf-adm-message-edit');
        buttonsEdit.forEach(button => button.addEventListener('click', () => {
            const messageBox = document.getElementById('message' + button.dataset.id);
            const message = messageBox.querySelector('.forum-message__content').textContent;
            const noteBox = messageBox.querySelector('.forum-message__note');
            const note = noteBox ? messageBox.querySelector('.forum-message__note div').textContent : '';
            showForm(button.dataset.id, message, note)
        }));
        const buttonsDelete = document.querySelectorAll('.jf-adm-message-delete');
        buttonsDelete.forEach(button => button.addEventListener('click', () => {
            const messageBox = document.getElementById('message' + button.dataset.id);
            const message = JokeForum.getSmartPreview(messageBox.querySelector('.forum-message__content'), 10);
            if (!confirm(`Вы действительно хотите удалить сообщение "${message}"?`)) {
                return;
            }
            deleteMessage(button.dataset.id)
        }));
        const buttonsBan = document.querySelectorAll('.jf-adm-user-ban');
        buttonsBan.forEach(button => button.addEventListener('click', () => {
            const isBanned = button.classList.contains('tool-btn_success');
            if (isBanned) {
                const name = button.closest('.forum-message__author').innerText;
                if (confirm(`Вы действительно хотите разбанить пользователя "${name}"?`)) {
                    unbanUser(button.dataset.id)
                }
                return;
            }
            showBanForm(button.dataset.id)
        }));
    }
})(window, document);