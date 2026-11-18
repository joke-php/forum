(function (window, document) {
    'use strict';

    document.addEventListener('DOMContentLoaded', init);

    const formBox = document.getElementById('form-box');
    let formVisible = false;

    const showFormTopic = (sectionId) => {
        if (formVisible) {
            document.querySelector('input[name=subject]').focus();
            return;
        }

        const ensureEnabledSendButton = () => {
            btn.disabled = subjectInput.value.length > 0 && textarea.value.length > 0
        }

        formVisible = true;
        // @todo В настройки
        const MIN_LENGTH = 10;
        const MAX_LENGTH = 5000;
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
        subjectInput.addEventListener('input', ensureEnabledSendButton);
        const text = JokeForum.createTextAreaInput('message', 'Введите текст сообщения', [], [], {});
        const textarea = text.querySelector('textarea');
        textarea.addEventListener('input', ensureEnabledSendButton);
        const buttons = JokeForum.createEl('div', ['forum-toolbar']);
        const btn = document.createElement('button');
        btn.disabled = true;
        const sectionInput = JokeForum.createHidden('sectionId', sectionId);

        btn.textContent = 'Отправить';
        btn.className = 'btn btn_primary';
        form.appendChild(messages);
        form.appendChild(subject);
        form.appendChild(text);
        form.appendChild(sectionInput);
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
                !validateLength(subjectInput.value, MIN_SUBJECT_LENGTH, MAX_SUBJECT_LENGTH)
                || !validateLength(textarea.value, MIN_LENGTH, MAX_LENGTH);
        }


        textarea.addEventListener('input', validateInput);
        subjectInput.addEventListener('input', validateInput);

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
                const response = await fetch('/topic', {
                    method: 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-Token': JokeForum.getCsrfToken(),
                    },
                    body: new FormData(form),
                });

                if (!response.ok && response.status === 403) {
                    showError('Вы не авторизованы или у вас нет прав на отправку сообщения');
                    subject.focus();
                    return;
                }

                const data = await response.json();
                if (data.success && data.topicUrl) {
                    window.location.href = data.topicUrl;
                } else {
                    showError(data.message || 'Произошла ошибка при добавлении темы');

                }
            } catch (error) {
                showError('Произошла ошибка при добавлении темы');
            }
        });
    }

    function init() {
        const buttonsNewTopic = document.querySelectorAll('.jf-new-topic');
        buttonsNewTopic.forEach(button => button.addEventListener('click', () => showFormTopic(button.dataset.section)));
    }
})(window, document);