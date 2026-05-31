document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('requestForm');
    const resultBox = document.getElementById('apiResult');

    form.addEventListener('submit', async function(e) {
        e.preventDefault();

        resultBox.style.display = 'block';
        resultBox.className = 'result-box';
        resultBox.innerHTML = '<p style="text-align:center;">⏳ Отправка...</p>';

        const data = {
            name: form.querySelector('[name="name"]').value.trim(),
            phone: form.querySelector('[name="phone"]').value.trim(),
            email: form.querySelector('[name="email"]').value.trim(),
            subject: form.querySelector('[name="subject"]').value,
            wishes: form.querySelector('[name="wishes"]').value.trim()
        };

        console.log('Отправляем данные:', data);

        try {
            const response = await fetch('api.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(data)
            });

            console.log('Статус ответа:', response.status);

            // Сначала получаем текст, чтобы увидеть что реально пришло
            const text = await response.text();
            console.log('Ответ сервера (текст):', text);

            // Пытаемся распарсить как JSON
            let result;
            try {
                result = JSON.parse(text);
            } catch (e) {
                console.error('Не валидный JSON. Пришло:', text);
                throw new Error('Сервер вернул не JSON. Проверьте консоль.');
            }

            if (result.success) {
                resultBox.className = 'result-box success';
                resultBox.innerHTML = `
                    <h3>✅ Успешно!</h3>
                    <p><strong>Логин:</strong> ${result.login}</p>
                    <p><strong>Пароль:</strong> ${result.password}</p>
                    <a href="edit.php" class="btn-primary" style="display:inline-block; margin-top:15px; padding:10px 20px; color:white;">
                        Войти в кабинет
                    </a>
                `;
                form.reset();
            } else {
                resultBox.className = 'result-box error';
                let errorsHtml = '<h3>❌ Ошибки:</h3><ul>';
                for (let field in result.errors) {
                  errorsHtml += `<li>${result.errors[field]}</li>`;
                }
                errorsHtml += '</ul>';
                resultBox.innerHTML = errorsHtml;
            }

        } catch (error) {
            console.error('Полная ошибка:', error);
            resultBox.className = 'result-box error';
            resultBox.innerHTML = `
                <h3>❌ Ошибка</h3>
                <p>${error.message}</p>
                <p style="font-size:0.9em; margin-top:10px;">
                    Откройте консоль (F12) для подробностей
                </p>
            `;
        }
    });
});
