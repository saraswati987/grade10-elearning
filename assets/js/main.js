/**
 * Grade 10 E-Learning Management System JavaScript
 * Handles tabs, form validation & quiz interactive runner
 */

document.addEventListener('DOMContentLoaded', () => {
    // 1. Tab Switching Handler
    const tabButtons = document.querySelectorAll('.tab-btn');
    tabButtons.forEach(btn => {
        btn.addEventListener('click', () => {
            const target = btn.getAttribute('data-target');
            
            // Remove active class from all buttons and panes
            document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
            document.querySelectorAll('.tab-pane').forEach(p => p.classList.remove('active'));

            // Set active class
            btn.classList.add('active');
            const targetPane = document.getElementById(target);
            if (targetPane) targetPane.classList.add('active');
        });
    });

    // 2. Interactive Timed Quiz Handler
    const quizForm = document.getElementById('quizForm');
    const timerDisplay = document.getElementById('timerDisplay');
    
    if (quizForm && timerDisplay) {
        let durationMins = parseInt(timerDisplay.getAttribute('data-duration')) || 10;
        let totalSeconds = durationMins * 60;

        const countdownInterval = setInterval(() => {
            totalSeconds--;
            let mins = Math.floor(totalSeconds / 60);
            let secs = totalSeconds % 60;

            timerDisplay.textContent = `${mins.toString().padStart(2, '0')}:${secs.toString().padStart(2, '0')}`;

            if (totalSeconds <= 0) {
                clearInterval(countdownInterval);
                alert("Time is up! Your quiz will now be submitted automatically.");
                quizForm.submit();
            }
        }, 1000);

        quizForm.addEventListener('submit', () => {
            clearInterval(countdownInterval);
        });
    }

    // 3. Password Confirmation Validation
    const regForm = document.getElementById('registerForm');
    if (regForm) {
        regForm.addEventListener('submit', (e) => {
            const pass = document.getElementById('password').value;
            const confirmPass = document.getElementById('confirm_password').value;

            if (pass !== confirmPass) {
                e.preventDefault();
                alert("Passwords do not match! Please check and try again.");
            }
        });
    }
});
