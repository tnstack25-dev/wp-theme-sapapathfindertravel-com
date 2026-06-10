function togglePassword(inputId) {
  const input = document.getElementById(inputId);
  const type = input.type === 'password' ? 'text' : 'password';
  input.type = type;
}

const passwordInput = document.getElementById('password');
if (passwordInput) {
  passwordInput.addEventListener('input', function () {
    const password = this.value;
    const strengthBars = document.querySelectorAll('.strength-bar');
    let strength = 0;

    if (password.length >= 6) strength++;
    if (password.length >= 10) strength++;
    if (/[A-Z]/.test(password) && /[a-z]/.test(password)) strength++;
    if (/[0-9]/.test(password) && /[^A-Za-z0-9]/.test(password)) strength++;

    strengthBars.forEach((bar, index) => {
      bar.className = 'strength-bar';
      if (index < strength) {
        if (strength <= 1) bar.classList.add('weak');
        else if (strength <= 2) bar.classList.add('medium');
        else bar.classList.add('strong');
      }
    });
  });
}

document.querySelectorAll('form').forEach(form => {
  form.addEventListener('submit', function () {
    const btn = this.querySelector('.auth-btn');
    if (btn) {
      btn.classList.add('loading');
      btn.innerHTML = '<span class="spinner"></span> Đang xử lý...';
    }
  });
});

document.querySelectorAll('.form-input').forEach(input => {
  input.addEventListener('focus', function () {
    this.parentElement.classList.add('focused');
  });
  input.addEventListener('blur', function () {
    this.parentElement.classList.remove('focused');
  });
});
