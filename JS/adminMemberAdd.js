function validateBirthdate() {
    var birthdateInput = document.getElementById("birthdate");
    var birthdate = new Date(birthdateInput.value);
    var today = new Date();
    var birthdateFeedback = document.getElementById("birthdate_feedback");

    if (!birthdateInput.value) {
        birthdateInput.classList.add("invalid");
        birthdateInput.classList.remove("valid");
        birthdateFeedback.innerHTML = '<span class="symbol">❌</span> Birth date cannot be empty.';
        birthdateFeedback.className = "feedback invalid";
        return false;
    }

    if (birthdate > today) {
        birthdateInput.classList.add("invalid");
        birthdateInput.classList.remove("valid");
        birthdateFeedback.innerHTML = '<span class="symbol">❌</span> Birth date cannot be today or a future date.';
        birthdateFeedback.className = "feedback invalid";
        return false;
    }

    var age = today.getFullYear() - birthdate.getFullYear();
    var monthDifference = today.getMonth() - birthdate.getMonth();
    var dayDifference = today.getDate() - birthdate.getDate();

    if (monthDifference < 0 || (monthDifference === 0 && dayDifference < 0)) {
        age--;
    }

    if (age < 15) {
        birthdateInput.classList.add("invalid");
        birthdateInput.classList.remove("valid");
        birthdateFeedback.innerHTML = '<span class="symbol">❌</span> You must be 15 years old or above.';
        birthdateFeedback.className = "feedback invalid";
        return false;
    }

    birthdateInput.classList.remove("invalid");
    birthdateInput.classList.add("valid");
    birthdateFeedback.innerHTML = '<span class="symbol">✔️</span> Birth date is valid.';
    birthdateFeedback.className = "feedback valid";
    return true;
}


function validateContactNumber() {
    var contactnumber = document.getElementById("contactnumber").value.trim();
    var validFormat = /^\d{10,11}$/;

    if (validFormat.test(contactnumber)) {
        return true;
    } else {
        return false;
    }
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
            
            if (fieldType === "contact number") {
                feedback.innerHTML = '<span class="symbol">❌</span>Contact number must be 10 or 11 digits.';
            } else {
                feedback.innerHTML = '<span class="symbol">❌</span>Invalid ' + fieldType + ' format.';
            }
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
                            feedback.innerHTML = '<span class="symbol">✔️</span>' + fieldType.charAt(0).toUpperCase() + fieldType.slice(1) + ' is valid.';
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

function validateForm() {
    var username = document.getElementById("username");
    var birthdate = document.getElementById("birthdate");
    var contactnumber = document.getElementById("contactnumber");
    var email = document.getElementById("email");
    var password = document.getElementById("password");
    var confirmPassword = document.getElementById("confirm_password");
    var profilePic = document.getElementById("profile_picture");

    if (username.classList.contains("invalid") || 
        email.classList.contains("invalid") || 
        birthdate.classList.contains("invalid") || 
        contactnumber.classList.contains("invalid") || 
        password.classList.contains("invalid") || 
        confirmPassword.classList.contains("invalid") ||
        profilePic.classList.contains("invalid")) {
        alert("Please ensure all fields are filled correctly before submitting.");
        return false;
    }

    if (username.value.trim() === "" || 
        email.value.trim() === "" || 
        birthdate.value.trim() === "" || 
        contactnumber.value.trim() === "" ||
        password.value.trim() === "" || 
        confirmPassword.value.trim() === "" ||
        profilePic.value.trim() === "") {
        alert("Please fill in all required fields.");
        return false;
    }
    
    return true;
}

window.onload = function() {
    checkDuplicate("username", "username_feedback", "username", isValidUsername);
    checkDuplicate("email", "email_feedback", "email", isValidEmail);
    checkDuplicate("contactnumber", "contactnumber_feedback", "contact number", validateContactNumber);

    document.getElementById("password").addEventListener('input', validatePassword);
    document.getElementById("confirm_password").addEventListener('input', validateConfirmPassword);
    document.getElementById("birthdate").addEventListener('change', validateBirthdate);
    document.getElementById("profile_picture").addEventListener('change', validateProfilePic);
};

function confirmCancel() {
    if (confirm("Are you sure you want to cancel? Any unsaved information will be lost.")) {
        window.location.href = 'adminAdmin.php';
    }
}