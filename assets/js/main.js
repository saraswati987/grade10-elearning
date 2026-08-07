/**
 * Grade 10 E-Learning — progressive enhancement only.
 * Every feature below has a working server-side equivalent; nothing here is
 * load-bearing for correctness.
 */
document.addEventListener('DOMContentLoaded', () => {

    /* --- Tabs (subject detail) ----------------------------------------- */
    document.querySelectorAll('.tab-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            const target = btn.getAttribute('data-target');
            document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
            document.querySelectorAll('.tab-pane').forEach(p => p.classList.remove('active'));
            btn.classList.add('active');
            const pane = document.getElementById(target);
            if (pane) pane.classList.add('active');
        });
    });

    /* --- Conditional form fields --------------------------------------- */
    // <select data-toggle-format> shows the [data-format="<value>"] block in
    // the same form and hides the others.
    document.querySelectorAll('[data-toggle-format]').forEach(select => {
        const apply = () => {
            select.form.querySelectorAll('[data-format]').forEach(group => {
                group.classList.toggle('is-hidden', group.dataset.format !== select.value);
            });
        };
        select.addEventListener('change', apply);
        apply();
    });

    /* --- Destructive actions ------------------------------------------- */
    document.querySelectorAll('form[data-confirm]').forEach(form => {
        form.addEventListener('submit', e => {
            if (!window.confirm(form.dataset.confirm)) e.preventDefault();
        });
    });

    // Fills a hidden input from a prompt before submitting (password resets).
    document.querySelectorAll('form[data-prompt]').forEach(form => {
        form.addEventListener('submit', e => {
            const field = form.elements[form.dataset.prompt];
            if (field && field.value) return; // already filled
            const value = window.prompt(form.dataset.promptLabel || 'Enter a value');
            if (!value) {
                e.preventDefault();
                return;
            }
            field.value = value;
        });
    });

    /* --- Quiz countdown ------------------------------------------------- */
    // The server holds the real deadline (see take_quiz.php); this is the
    // visible clock and the courtesy auto-submit.
    const quizForm = document.getElementById('quizForm');
    const timer = document.getElementById('timerDisplay');

    if (quizForm && timer) {
        let remaining = parseInt(timer.dataset.remaining, 10);
        if (!Number.isFinite(remaining)) remaining = 0;
        let submitted = false;

        const render = () => {
            const safe = Math.max(0, remaining);
            const mins = String(Math.floor(safe / 60)).padStart(2, '0');
            const secs = String(safe % 60).padStart(2, '0');
            timer.textContent = `${mins}:${secs}`;
            timer.classList.toggle('is-urgent', safe <= 60);
        };

        render();
        const tick = setInterval(() => {
            remaining--;
            render();
            if (remaining <= 0) {
                clearInterval(tick);
                if (!submitted) {
                    submitted = true;
                    quizForm.submit();
                }
            }
        }, 1000);

        quizForm.addEventListener('submit', () => {
            submitted = true;
            clearInterval(tick);
        });
    }

    /* --- Registration: confirm password --------------------------------- */
    const regForm = document.getElementById('registerForm');
    if (regForm) {
        const confirmField = regForm.elements.confirm_password;
        regForm.addEventListener('submit', e => {
            if (regForm.elements.password.value !== confirmField.value) {
                e.preventDefault();
                confirmField.setCustomValidity('Passwords do not match.');
                confirmField.reportValidity();
            }
        });
        confirmField.addEventListener('input', () => confirmField.setCustomValidity(''));
    }
});
