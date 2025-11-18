function initializeDropdown() {
    var dropdownBtn = document.querySelector('.dropbtn');
    var dropdownContent = document.querySelector('.dropdown-content');

    if (dropdownBtn && dropdownContent) {
        dropdownBtn.addEventListener('click', function (e) {
            e.preventDefault();
            dropdownContent.style.display = dropdownContent.style.display === 'block' ? 'none' : 'block';
        });

        window.addEventListener('click', function (e) {
            if (!e.target.matches('.dropbtn')) {
                dropdownContent.style.display = 'none';
            }
        });
    }
}

function initializeOTPInputs() {
    const inputs = document.querySelectorAll('.number input');
    const otpVerifyBtn = document.querySelector('.otpVerifyBtn');

    function restrictInput(event) {
        const key = event.key;
        const keyCode = event.keyCode || event.which;
        if (!/^\d$/.test(key) && keyCode !== 8) {
            event.preventDefault();
        }
    }

    function handleInput(event, index) {
        const currentInput = event.target;
        const nextInput = inputs[index + 1];
        if (currentInput.value.length === 1 && nextInput) {
            nextInput.focus();
        }
        otpVerifyBtn.disabled = !Array.from(inputs).every(input => input.value.length === 1);
    }

    function handleKeyDown(event, index) {
        const currentInput = event.target;
        const prevInput = inputs[index - 1];

        if (event.key === 'Backspace') {
            if (currentInput.value.length === 0 && prevInput) {
                event.preventDefault();
                prevInput.focus();
                prevInput.value = ''; 
            }
        }
    }

    inputs.forEach(function (input, index) {
        input.addEventListener('keypress', restrictInput);
        input.addEventListener('input', (event) => handleInput(event, index));
        input.addEventListener('keydown', (event) => handleKeyDown(event, index));
    });
}

function initializeLoginTabs() {
    const tabs = document.querySelectorAll('.tab-button');
    const forms = document.querySelectorAll('.login-form');

    tabs.forEach(tab => {
        tab.addEventListener('click', () => {
            const tabName = tab.getAttribute('data-tab');

            tabs.forEach(t => t.classList.remove('active'));
            tab.classList.add('active');

            forms.forEach(form => {
                if (form.id === `${tabName}LoginForm`) {
                    form.classList.remove('hidden');
                } else {
                    form.classList.add('hidden');
                }
            });
        });
    });
}

function initializeFAQ() {
    const faqItems = document.querySelectorAll('.faq-item');

    faqItems.forEach(item => {
        item.addEventListener('click', () => {
            item.classList.toggle('active');
        });
    });
}
function initializeTestimonials() {
    const track = document.querySelector('.testimonial-track');
    const testimonials = track.querySelectorAll('.testimonial');
    const testimonialWidth = testimonials[0].offsetWidth + parseInt(window.getComputedStyle(testimonials[0]).marginRight);
    let position = 0;
    const speed = 0.5;

    testimonials.forEach(testimonial => {
        const clone = testimonial.cloneNode(true);
        track.appendChild(clone);
    });

    function slide() {
        position -= speed;
        if (position <= -(testimonialWidth * testimonials.length)) {
            position += testimonialWidth * testimonials.length;
            track.style.transition = 'none';
            track.style.transform = `translateX(${position}px)`;
            track.offsetHeight;
            track.style.transition = 'transform 0.5s ease';
        } else {
            track.style.transform = `translateX(${position}px)`;
        }
    }

    function animate() {
        slide();
        requestAnimationFrame(animate);
    }

    animate();
}

function initializePasswordToggle() {
    const toggles = document.querySelectorAll('.toggle-password');
    toggles.forEach(toggle => {
        toggle.addEventListener('click', function() {
            const targetId = this.getAttribute('data-target');
            const passwordInput = document.getElementById(targetId);
            const icon = this.querySelector('i');

            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                passwordInput.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        });
    });
}

document.addEventListener('DOMContentLoaded', function () {
    if (document.querySelector('.dropbtn')) {
        initializeDropdown();
    }

    if (document.querySelector('.number')) {
        initializeOTPInputs();
    }

    if (document.querySelector('.tab-button')) {
        initializeLoginTabs();
    }

    if (document.querySelector('.faq-item')) {
        initializeFAQ();
    }

    if (document.querySelector('.testimonial-track')) {
        initializeTestimonials();
    }

    if (document.querySelector('.toggle-password')) {
        initializePasswordToggle();
    }


});