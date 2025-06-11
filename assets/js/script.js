// Aguarda o carregamento do DOM
document.addEventListener('DOMContentLoaded', () => {
    // Rolagem suave para âncoras internas
    const links = document.querySelectorAll('a[href^="#"]');
    links.forEach(link => {
        link.addEventListener('click', function (e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                target.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        });
    });

    // Validação e simulação de envio do formulário
    const form = document.querySelector('form');
    if (form) {
        form.addEventListener('submit', function (e) {
            const name = document.getElementById('name');
            const email = document.getElementById('email');
            const message = document.getElementById('message');

            // Validação simples
            if (!name.value || !email.value || !message.value) {
                alert('Por favor, preencha todos os campos antes de enviar.');
                e.preventDefault(); // Impede o envio
                return;
            }

            // Simula envio e feedback visual
            alert(`Obrigado, ${name.value}! Sua mensagem foi enviada com sucesso.`);

            // Opcional: Limpar o formulário após envio
            // e.preventDefault(); // Descomente se quiser bloquear o envio real via mailto
            // form.reset();
        });
    }
});
