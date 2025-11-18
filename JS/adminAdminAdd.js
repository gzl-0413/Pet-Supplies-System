function validatePassword() {
    var password = document.getElementById("password").value.trim();
    var passwordFeedback = document.getElementById("password_feedback");

    var hasDigit = /\d/;
    var hasAlphabet = /[a-zA-Z]/;
    var hasSymbol = /[!@#$%^&*(),.?":{}|<>]/;
    var hasUppercase = /[A-Z]/;
    var minLength = password.length >= 8;

    var validationErrors = [];

    if (password === "") {
        validationErrors.push("Password cannot be empty.");
    }

    if (!hasDigit.test(password)) {
        validationErrors.push("Password must contain at least 1 digit.");
    }
    if (!hasAlphabet.test(password)) {
        validationErrors.push("Password must contain at least 1 alphabet.");
    }
    if (!hasSymbol.test(password)) {
        validationErrors.push("Password must contain at least 1 symbol.");
    }
    if (!hasUppercase.test(password)) {
        validationErrors.push("Password must contain at least 1 uppercase letter.");
    }
    if (!minLength) {
        validationErrors.push("Password must be at least 8 characters long.");
    }

    if (validationErrors.length > 0) {
        document.getElementById("password").classList.add("invalid");
        document.getElementById("password").classList.remove("valid");
        passwordFeedback.innerHTML = '<span class="symbol">❌</span>' + validationErrors.join("<br><span class='symbol'>❌</span>");
        passwordFeedback.className = "feedback invalid";
    } else {
        document.getElementById("password").classList.remove("invalid");
        document.getElementById("password").classList.add("valid");
        passwordFeedback.innerHTML = '<span class="symbol">✔️</span>Password is strong.';
        passwordFeedback.className = "feedback valid";
    }
}

function validateConfirmPassword() {
    var password = document.getElementById("password").value.trim();
    var confirmPassword = document.getElementById("confirm_password").value.trim();
    var confirmPasswordFeedback = document.getElementById("confirm_password_feedback");

    if (confirmPassword !== password) {
        document.getElementById("confirm_password").classList.add("invalid");
        document.getElementById("confirm_password").classList.remove("valid");
        confirmPasswordFeedback.innerHTML = '<span class="symbol">❌</span>Passwords do not match.';
        confirmPasswordFeedback.className = "feedback invalid";
    } else {
        document.getElementById("confirm_password").classList.remove("invalid");
        document.getElementById("confirm_password").classList.add("valid");
        confirmPasswordFeedback.innerHTML = '<span class="symbol">✔️</span>Passwords match.';
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

    field.addEventListener('input', function() {
        var fieldValue = field.value.trim();

        if (fieldValue === "") {
            field.classList.remove("valid");
            field.classList.add("invalid");
            feedback.innerHTML = '<span class="symbol">❌</span>This field cannot be empty.';
            feedback.className = "feedback invalid";
            return;
        }

        if (!validationFn(fieldValue)) {
            field.classList.remove("valid");
            field.classList.add("invalid");
            feedback.innerHTML = '<span class="symbol">❌</span>Invalid ' + fieldType + ' format.';
            feedback.className = "feedback invalid";
            return;
        }

        var xhr = new XMLHttpRequest();
        xhr.open("POST", "checkduplicate.php", true);
        xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");

        xhr.onreadystatechange = function() {
            if (xhr.readyState === 4) {
                if (xhr.status === 200) {
                    console.log("Response from server:", xhr.responseText);

                    try {
                        var response = JSON.parse(xhr.responseText);

                        if (response.status === 'duplicate') {
                            field.classList.remove("valid");
                            field.classList.add("invalid");
                            feedback.innerHTML = '<span class="symbol">❌</span>' + fieldType.charAt(0).toUpperCase() + fieldType.slice(1) + ' already exists.';
                            feedback.className = "feedback invalid";
                        } else if (response.status === 'available') {
                            field.classList.remove("invalid");
                            field.classList.add("valid");
                            feedback.innerHTML = '<span class="symbol">✔️</span>' + fieldType.charAt(0).toUpperCase() + fieldType.slice(1) + ' is available.';
                            feedback.className = "feedback valid";
                        } else {
                            field.classList.remove("valid");
                            field.classList.add("invalid");
                            feedback.innerHTML = '<span class="symbol">❌</span>Error in checking ' + fieldType + '.';
                            feedback.className = "feedback invalid";
                        }
                    } catch (e) {
                        console.error("Error parsing JSON response:", e);
                        field.classList.remove("valid");
                        field.classList.add("invalid");
                        feedback.innerHTML = '<span class="symbol">❌</span>Unexpected response format from the server.';
                        feedback.className = "feedback invalid";
                    }
                } else {
                    console.error("Error: Could not contact the server.");
                    field.classList.remove("valid");
                    field.classList.add("invalid");
                    feedback.innerHTML = '<span class="symbol">❌</span>Unable to check ' + fieldType + ' due to server error.';
                    feedback.className = "feedback invalid";
                }
            }
        };

        xhr.send("field=" + fieldType + "&value=" + fieldValue);
    });
}

function validateProfilePic(event) {
    var file = event.target.files[0];
    var feedback = document.getElementById('profile_picture_feedback');
    var validTypes = ['image/jpeg', 'image/png', 'image/gif'];
    var maxSize = 2 * 1024 * 1024; // 2MB

    if (!file) {
        feedback.classList.add("invalid");
        feedback.classList.remove("valid");
        feedback.innerHTML = '<span class="symbol">❌</span>No image selected.';
        feedback.className = "feedback invalid";
        return;
    }

    if (!validTypes.includes(file.type)) {
        feedback.classList.add("invalid");
        feedback.classList.remove("valid");
        feedback.innerHTML = '<span class="symbol">❌</span>Invalid file type. Please upload an image (JPG, PNG, or GIF).';
        feedback.className = "feedback invalid";
        return;
    }

    if (file.size > maxSize) {
        feedback.classList.add("invalid");
        feedback.classList.remove("valid");
        feedback.innerHTML = '<span class="symbol">❌</span>File is too large. Please upload an image less than 2MB.';
        feedback.className = "feedback invalid";
        return;
    }

    var reader = new FileReader();
    reader.onload = function(e) {
        var output = document.getElementById('image_preview');
        output.innerHTML = '<img src="' + e.target.result + '" alt="Selected Profile Picture" style="max-width: 150px;">';
        feedback.classList.add("valid");
        feedback.classList.remove("invalid");
        feedback.innerHTML = '<span class="symbol">✔️</span>Valid image selected.';
        feedback.className = "feedback valid";
    };
    reader.readAsDataURL(file);
}


function validateForm() {
    var username = document.getElementById("username");
    var email = document.getElementById("email");
    var password = document.getElementById("password");
    var confirmPassword = document.getElementById("confirm_password");
    var profilePic = document.getElementById("profile_picture");

    if (username.classList.contains("invalid") || 
        email.classList.contains("invalid") || 
        password.classList.contains("invalid") || 
        confirmPassword.classList.contains("invalid") ||
        profilePic.classList.contains("invalid")) {
        alert("Please ensure all fields are filled correctly before submitting.");
        return false;
    }
    
    if (username.value.trim() === "" || 
        email.value.trim() === "" || 
        password.value.trim() === "" || 
        confirmPassword.value.trim() === "" ||
        profilePic.value.trim() === "") {
        alert("Please fill in all required fields.");
        return false;
    }

    return true;
}

window.onload = function() {
    checkDuplicate("username", "username_feedback", "admin name", isValidUsername);
    checkDuplicate("email", "email_feedback", "admin email", isValidEmail);

    document.getElementById("password").addEventListener('input', validatePassword);
    document.getElementById("confirm_password").addEventListener('input', validateConfirmPassword);
    document.getElementById("profile_picture").addEventListener('change', validateProfilePic);
};

function previewImage(event) {
    var file = event.target.files[0];
    var output = document.getElementById('image_preview');

    if (file) {
        var reader = new FileReader();
        reader.onload = function(e) {
            output.innerHTML = '<img src="' + e.target.result + '" alt="Selected Profile Picture" style="max-width: 150px;">';
        };
        reader.readAsDataURL(file);
    } else {
        output.innerHTML = '';
    }
}

function confirmCancel() {
    if (confirm("Are you sure you want to cancel? Any unsaved information will be lost.")) {
        window.location.href = 'adminAdmin.php';
    }
}