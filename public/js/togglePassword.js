// system for password field
const passwordInput = document.getElementById('password');
const togglePasswordButton = document.getElementById('togglePassword');
const eyeIcon = document.getElementById('eyeIcon');
const eyeSlashIcon = document.getElementById('eyeSlashIcon');

if (togglePasswordButton && passwordInput && eyeIcon && eyeSlashIcon) {
    togglePasswordButton.addEventListener('click', function() {
        const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
        passwordInput.setAttribute('type', type);

        eyeIcon.classList.toggle('hidden');
        eyeSlashIcon.classList.toggle('hidden');
    });
}

// system for confirm password field
const confirmPasswordInput = document.getElementById('confirmPassword');
const toggleConfirmPasswordButton = document.getElementById('toggleConfirmPassword');
const eyeIconConfirm = document.getElementById('eyeIconConfirm');
const eyeSlashIconConfirm = document.getElementById('eyeSlashIconConfirm');

if (toggleConfirmPasswordButton && confirmPasswordInput && eyeIconConfirm && eyeSlashIconConfirm) {
    toggleConfirmPasswordButton.addEventListener('click', function() {
        const type = confirmPasswordInput.getAttribute('type') === 'password' ? 'text' : 'password';
        confirmPasswordInput.setAttribute('type', type);

        eyeIconConfirm.classList.toggle('hidden');
        eyeSlashIconConfirm.classList.toggle('hidden');
    });
}
