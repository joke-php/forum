(function (window, document) {
    'use strict';

    document.addEventListener('DOMContentLoaded', init);

    const formBox = document.getElementById('form-box');
    let formVisible = false;

    const showFormTopic = (topicId) => {
        if (formVisible) {
            document.querySelector('input[name=subject]').focus();
            return;
        }
        const itemSubject = document.querySelector('#topic' + topicId + ' .topic-title');

        const ensureEnabledSendButton = () => {
            btn.disabled = subjectInput.value.length > 0
        }

        formVisible = true;
        // @todo В настройки
        const MIN_SUBJECT_LENGTH = 10;
        const MAX_SUBJECT_LENGTH = 255;

        const form = document.createElement('form');
        form.classList.add('form');
        form.method = 'post';
        form.action = '/topic';
        const messages = JokeForum.createEl('div', ['message', 'message_error']);
        messages.style.display = 'none';
        const subject = JokeForum.createTextInput('text', 'subject', 'Введите название темы', [], [], {});
        const subjectInput = subject.querySelector('input');
        subjectInput.value = itemSubject.textContent.trim();
        subjectInput.addEventListener('input', ensureEnabledSendButton);
        const buttons = JokeForum.createEl('div', ['forum-toolbar']);
        const btn = document.createElement('button');
        btn.disabled = true;

        btn.textContent = 'Отправить';
        btn.className = 'btn btn_primary';
        form.appendChild(subject);
        buttons.appendChild(btn);
        form.appendChild(buttons);
        formBox.appendChild(form);
        subject.focus();

        const validateLength = (text, min, max) => {
            const length = text.length;

            return (length >= min && length <= max);
        }

        function validateInput() {
            messages.textContent = '';
            messages.style.display = 'none';

            btn.disabled =
                !validateLength(subjectInput.value, MIN_SUBJECT_LENGTH, MAX_SUBJECT_LENGTH);
        }


        subjectInput.addEventListener('input', validateInput);
        validateInput();

        const showError = (error) => {
            messages.textContent = error;
            messages.style.display = 'block';
        }

        form.addEventListener('submit', async function (event) {
            event.preventDefault();
            event.stopPropagation();

            const subjectLength = subjectInput.value.length;
            if (subjectLength < MIN_SUBJECT_LENGTH || subjectLength > MAX_SUBJECT_LENGTH) {
                showError(
                    subjectLength < MIN_SUBJECT_LENGTH
                        ? `Название темы слишком короткое. Минимум ${MIN_SUBJECT_LENGTH} символов.`
                        : `Название темы слишком длинное. Максимум ${MAX_SUBJECT_LENGTH} символов.`
                );
                return;
            }

            try {
                const response = await fetch('/topic/' + topicId, {
                    method: 'PUT',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-Token': JokeForum.getCsrfToken(),
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        subject: subjectInput.value,
                    }),
                });

                if (!response.ok && response.status === 403) {
                    showError('Вы не авторизованы или у вас нет прав редактирование темы');
                    subject.focus();
                    return;
                }

                const data = await response.json();
                if (data.success) {
                    window.location.reload();
                } else {
                    showError(data.message || 'Произошла ошибка при обновлении темы');

                }
            } catch (error) {
                showError('Произошла ошибка при обновлении темы');
            }
        });
    }
    const deleteTopic = (topicId) => {
        try {
            fetch('/topic/' + topicId, {
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
                    alert('Ошибка удаления темы')
                }
            })
        } catch (error) {
            console.error('error', error)
        }
    }

    function init() {
        const buttonsNewTopic = document.querySelectorAll('.jf-adm-topic-edit');
        buttonsNewTopic.forEach(button => button.addEventListener('click', event => {
            event.preventDefault();
            event.stopPropagation();

            showFormTopic(button.dataset.id);
        }));
        const buttonsDelete = document.querySelectorAll('.jf-adm-topic-delete');
        buttonsDelete.forEach(button => button.addEventListener('click', event => {
            event.preventDefault();
            event.stopPropagation();
            const itemSubject = document.querySelector('#topic' + button.dataset.id + ' .topic-title');
            console.log(itemSubject)

            const message = JokeForum.getSmartPreview(itemSubject, 10);
            if (!confirm(`Вы действительно хотите удалить тему "${message}"?`)) {
                return;
            }
            deleteTopic(button.dataset.id)
        }));

    }
})(window, document);