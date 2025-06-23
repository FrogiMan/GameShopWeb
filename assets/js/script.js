document.addEventListener('DOMContentLoaded', function() {
    const animateElements = document.querySelectorAll('.game-card, .hero, section');
    animateElements.forEach((el, index) => {
        el.classList.add('fade-in', `delay-${index % 3}`);
    });

    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function(e) {
            e.preventDefault();
            document.querySelector(this.getAttribute('href')).scrollIntoView({
                behavior: 'smooth'
            });
        });
    });

    const forms = document.querySelectorAll('form');
    forms.forEach(form => {
        form.addEventListener('submit', function(e) {
            const requiredFields = this.querySelectorAll('[required]');
            let isValid = true;
            
            requiredFields.forEach(field => {
                if (!field.value.trim()) {
                    field.style.borderColor = '#d63031';
                    isValid = false;
                }
            });
            
            if (!isValid) {
                e.preventDefault();
                if (!this.querySelector('.form-error')) {
                    const errorEl = document.createElement('div');
                    errorEl.className = 'form-error';
                    errorEl.textContent = 'Пожалуйста, заполните все обязательные поля';
                    errorEl.style.color = '#d63031';
                    errorEl.style.marginTop = '1rem';
                    this.appendChild(errorEl);
                }
            }
        });

        const inputs = form.querySelectorAll('input, textarea');
        inputs.forEach(input => {
            input.addEventListener('input', function() {
                this.style.borderColor = '';
                const errorEl = form.querySelector('.form-error');
                if (errorEl) errorEl.remove();
            });
        });
    });

    const paymentMethods = document.querySelectorAll('.payment-method input[type="radio"]');
    paymentMethods.forEach(method => {
        method.addEventListener('change', function() {
            document.querySelectorAll('.payment-method').forEach(el => {
                el.style.borderColor = '#eee';
            });
            this.closest('.payment-method').style.borderColor = '#6c5ce7';
        });
    });

    if (paymentMethods.length > 0) {
        const checkedMethod = document.querySelector('.payment-method input[type="radio"]:checked');
        if (checkedMethod) {
            checkedMethod.closest('.payment-method').style.borderColor = '#6c5ce7';
        } else {
            paymentMethods[0].closest('.payment-method').style.borderColor = '#6c5ce7';
            paymentMethods[0].checked = true;
        }
    }
});

function startCartTimer() {
    const CART_EXPIRE_TIME = 60 * 60 * 1000;
    let cartTimer = localStorage.getItem('cartTimer');
    
    if (!cartTimer) {
        localStorage.setItem('cartTimer', Date.now());
    } else if (Date.now() - cartTimer > CART_EXPIRE_TIME) {
        clearCart();
    }
    
    document.addEventListener('click', function() {
        localStorage.setItem('cartTimer', Date.now());
    });
}

function clearCart() {
    fetch('/clear_cart.php', { method: 'POST' })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                localStorage.removeItem('cartTimer');
                window.location.reload();
            }
        });
}

if (window.location.pathname.includes('cart.php')) {
    startCartTimer();
}
