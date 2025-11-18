console.log('register.js loaded');

document.addEventListener('DOMContentLoaded', function () {
    console.log('DOM fully loaded');

    const isRegisterPage = document.getElementById('registerForm') !== null;
    const isNewPasswordPage = document.getElementById('newPasswordForm') !== null;

    if (isRegisterPage) {
        initializeRegisterForm();
    } else if (isNewPasswordPage) {
        initializeNewPasswordForm();
    }
});

function initializeRegisterForm() {
    const fields = ['username', 'birthdate', 'contactnumber', 'email', 'password', 'repeat', 'photo'];

    fields.forEach(field => {
        const input = document.getElementById(field);
        if (input) {
            if (field === 'photo') {
                input.addEventListener('change', validateProfilePic);
            } else {
                input.addEventListener('input', () => validateField(field));
                input.addEventListener('blur', () => validateField(field));
            }
        }
    });

    document.getElementById('registerForm').addEventListener('submit', function (event) {
        fields.forEach(validateField);
        if (document.querySelectorAll('.invalid').length > 0) {
            event.preventDefault();
            alert('Please correct the errors before submitting.');
        }
    });
}

function initializeNewPasswordForm() {
    const fields = ['password', 'repeat'];

    fields.forEach(field => {
        const input = document.getElementById(field);
        if (input) {
            input.addEventListener('input', () => validateField(field));
            input.addEventListener('blur', () => validateField(field));
        }
    });

    document.getElementById('newPasswordForm').addEventListener('submit', function (event) {
        fields.forEach(validateField);
        if (document.querySelectorAll('.invalid').length > 0) {
            event.preventDefault();
            alert('Please correct the errors before submitting.');
        }
    });
}

function validateField(field) {
    const input = document.getElementById(field);
    const feedback = document.getElementById(`${field}Feedback`);

    if (!input || !feedback) return;

    const value = input.value.trim();

    switch (field) {
        case 'username':
            if (value === '') {
                setInvalid(input, feedback, 'Username cannot be empty.');
            } else {
                checkDuplicate('username', 'usernameFeedback', 'username', isValidUsername);
            }
            break;
        case 'email':
            if (value === '') {
                setInvalid(input, feedback, 'Email cannot be empty.');
            } else if (!isValidEmail(value)) {
                setInvalid(input, feedback, 'Please enter a valid email address.');
            } else {
                checkDuplicate('email', 'emailFeedback', 'email', isValidEmail);
            }
            break;
        case 'password':
            validatePassword();
            break;
        case 'repeat':
            validateConfirmPassword();
            break;
        case 'photo':
            validateProfilePic();
            break;
        case 'contactnumber':
            validateContactNumber(input, feedback);
            break;
        case 'birthdate':
            validateBirthdate(input, feedback);
            break;
        default:
            if (value === '') {
                setInvalid(input, feedback, `${field.charAt(0).toUpperCase() + field.slice(1)} cannot be empty.`);
            } else {
                setValid(input, feedback, '');
            }
    }
}

function validatePassword() {
    var password = document.getElementById("password").value.trim();
    var passwordFeedback = document.getElementById("passwordFeedback");

    var requirements = [
        { regex: /\d/, message: "At least 1 digit" },
        { regex: /[a-z]/, message: "At least 1 lowercase letter" },
        { regex: /[A-Z]/, message: "At least 1 uppercase letter" },
        { regex: /[!@#$%^&*(),.?":{}|<>]/, message: "At least 1 symbol" },
        { test: (pwd) => pwd.length >= 8, message: "At least 8 characters long" }
    ];

    var validationResults = requirements.map(req => ({
        passed: req.regex ? req.regex.test(password) : req.test(password),
        message: req.message
    }));

    var allPassed = validationResults.every(result => result.passed);

    var feedbackHTML = validationResults.map(result =>
        `<span class="${result.passed ? 'valid' : 'invalid'}">${result.passed ? '✔' : '✖'} ${result.message}</span>`
    ).join('<br>');

    if (password === "") {
        feedbackHTML = '<span class="invalid">✖ Password cannot be empty.</span>';
        allPassed = false;
    }

    passwordFeedback.innerHTML = feedbackHTML;

    if (allPassed) {
        document.getElementById("password").classList.remove("invalid");
        document.getElementById("password").classList.add("valid");
        passwordFeedback.classList.remove("invalid");
        passwordFeedback.classList.add("valid");
    } else {
        document.getElementById("password").classList.add("invalid");
        document.getElementById("password").classList.remove("valid");
        passwordFeedback.classList.add("invalid");
        passwordFeedback.classList.remove("valid");
    }
}

function validateConfirmPassword() {
    var password = document.getElementById("password").value.trim();
    var confirmPassword = document.getElementById("repeat").value.trim();
    var confirmPasswordFeedback = document.getElementById("repeatFeedback");

    if (password === "" && confirmPassword === "") {
        document.getElementById("repeat").classList.add("invalid");
        document.getElementById("repeat").classList.remove("valid");
        confirmPasswordFeedback.innerHTML = 'Password cannot be empty.';
        confirmPasswordFeedback.className = "feedback invalid";
    } else if (confirmPassword === "") {
        document.getElementById("repeat").classList.add("invalid");
        document.getElementById("repeat").classList.remove("valid");
        confirmPasswordFeedback.innerHTML = 'Please confirm your password.';
        confirmPasswordFeedback.className = "feedback invalid";
    } else if (confirmPassword !== password) {
        document.getElementById("repeat").classList.add("invalid");
        document.getElementById("repeat").classList.remove("valid");
        confirmPasswordFeedback.innerHTML = 'Passwords do not match.';
        confirmPasswordFeedback.className = "feedback invalid";
    } else {
        document.getElementById("repeat").classList.remove("invalid");
        document.getElementById("repeat").classList.add("valid");
        confirmPasswordFeedback.innerHTML = 'Passwords match.';
        confirmPasswordFeedback.className = "feedback valid";
    }
}

function isValidUsername(username) {
    return username.length > 0;
}

function isValidEmail(email) {
    var re = /^[^\s@]+@[^\s@]+\.(com|org|net|edu|gov)$/;
    return re.test(email);
}

function checkDuplicate(fieldId, feedbackId, fieldType, validationFn) {
    var field = document.getElementById(fieldId);
    var feedback = document.getElementById(feedbackId);

    if (!field || !feedback) {
        console.error(`Element not found: ${!field ? fieldId : feedbackId}`);
        return;
    }

    field.addEventListener('input', function () {
        var fieldValue = field.value.trim();

        if (fieldValue === "") {
            setInvalid(field, feedback, 'This field cannot be empty.');
            return;
        }

        if (!validationFn(fieldValue)) {
            setInvalid(field, feedback, `Invalid ${fieldType} format.`);
            return;
        }

        var xhr = new XMLHttpRequest();
        xhr.open("POST", "checkDuplicateRegister.php", true);
        xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");

        xhr.onreadystatechange = function () {
            if (xhr.readyState === 4) {
                if (xhr.status === 200) {
                    console.log("Response from server:", xhr.responseText);

                    try {
                        var response = JSON.parse(xhr.responseText);

                        if (response.status === 'duplicate') {
                            setInvalid(field, feedback, `${fieldType.charAt(0).toUpperCase() + fieldType.slice(1)} already exists.`);
                        } else if (response.status === 'available') {
                            setValid(field, feedback, `${fieldType.charAt(0).toUpperCase() + fieldType.slice(1)} is available.`);
                        } else {
                            setInvalid(field, feedback, `Error in checking ${fieldType}.`);
                        }
                    } catch (e) {
                        console.error("Error parsing JSON response:", e);
                        setInvalid(field, feedback, 'Unexpected response format from the server.');
                    }
                } else {
                    console.error("Error: Could not contact the server.");
                    setInvalid(field, feedback, `Unable to check ${fieldType} due to server error.`);
                }
            }
        };

        xhr.send("field=" + fieldType + "&value=" + encodeURIComponent(fieldValue));
    });
}


function validateProfilePic(event) {
    var file = event.target.files[0];
    var feedback = document.getElementById('photoFeedback');
    var validTypes = ['image/jpeg', 'image/png', 'image/gif'];
    var maxSize = 1 * 1024 * 1024;

    if (!file) {
        setInvalid(event.target, feedback, 'No image selected.');
        return;
    }

    if (!validTypes.includes(file.type)) {
        setInvalid(event.target, feedback, 'Invalid file type. Please upload an image (JPG, PNG, or GIF).');
        return;
    }

    if (file.size > maxSize) {
        setInvalid(event.target, feedback, 'File is too large. Please upload an image less than 1MB.');
        return;
    }

    var reader = new FileReader();
    reader.onload = function (e) {
        var output = document.getElementById('photoPreview');
        output.src = e.target.result;
        setValid(event.target, feedback, 'Valid image selected.');
    };
    reader.readAsDataURL(file);
}

function validateForm() {
    var fields = ['username', 'birthdate', 'contactnumber', 'email', 'password', 'repeat', 'photo'];
    var isValid = true;

    fields.forEach(field => {
        var element = document.getElementById(field);
        if (element.classList.contains('invalid') || element.value.trim() === '') {
            isValid = false;
        }
    });

    if (!isValid) {
        alert('Please ensure all fields are filled correctly before submitting.');
    }

    return isValid;
}

function validateContactNumber() {
    var contactNumber = document.getElementById("contactnumber").value.trim();
    var contactNumberFeedback = document.getElementById("contactnumberFeedback");

    var phoneRegex = /^\+?[0-9]{10,14}$/;

    var validationErrors = [];

    if (contactNumber === "") {
        validationErrors.push("Contact number cannot be empty.");
    }
    if (!phoneRegex.test(contactNumber)) {
        validationErrors.push("Invalid contact number format. Please enter 10-14 digits.");
    }

    if (validationErrors.length > 0) {
        document.getElementById("contactnumber").classList.add("invalid");
        document.getElementById("contactnumber").classList.remove("valid");
        contactNumberFeedback.innerHTML = validationErrors.join("<br>");
        contactNumberFeedback.className = "feedback invalid";
    } else {
        document.getElementById("contactnumber").classList.remove("invalid");
        document.getElementById("contactnumber").classList.add("valid");
        contactNumberFeedback.innerHTML = 'Valid contact number.';
        contactNumberFeedback.className = "feedback valid";
    }
}

function validateBirthdate() {
    var birthdate = document.getElementById("birthdate").value;
    var birthdateFeedback = document.getElementById("birthdateFeedback");

    var today = new Date();
    var birthDate = new Date(birthdate);
    var age = today.getFullYear() - birthDate.getFullYear();

    var validationErrors = [];

    if (birthdate === "") {
        validationErrors.push("Birthdate cannot be empty.");
    }
    if (isNaN(birthDate.getTime())) {
        validationErrors.push("Invalid date format.");
    }
    if (birthDate > today) {
        validationErrors.push("Birthdate cannot be in the future.");
    }
    if (age < 15) {
        validationErrors.push("You must be at least 15 years old to register.");
    }

    if (validationErrors.length > 0) {
        document.getElementById("birthdate").classList.add("invalid");
        document.getElementById("birthdate").classList.remove("valid");
        birthdateFeedback.innerHTML = validationErrors.join("<br>");
        birthdateFeedback.className = "feedback invalid";
    } else {
        document.getElementById("birthdate").classList.remove("invalid");
        document.getElementById("birthdate").classList.add("valid");
        birthdateFeedback.innerHTML = 'Valid birthdate.';
        birthdateFeedback.className = "feedback valid";
    }
}

function setInvalid(element, feedback, message) {
    element.classList.add('invalid');
    element.classList.remove('valid');
    feedback.textContent = message;
    feedback.className = 'feedback invalid';
}

function setValid(element, feedback, message) {
    element.classList.remove('invalid');
    element.classList.add('valid');
    feedback.textContent = message;
    feedback.className = 'feedback valid';
}
