function isValidUsername() {
    var usernameInput = document.getElementById("username");
    var usernameFeedback = document.getElementById("username_feedback");
    var username = usernameInput.value.trim();

    if (username.length === 0) {
        usernameInput.classList.add("invalid");
        usernameInput.classList.remove("valid");
        usernameFeedback.innerHTML = '<span class="symbol">❌</span>This field cannot be empty.';
        usernameFeedback.className = "feedback invalid";
    } else {
        usernameInput.classList.remove("invalid");
        usernameInput.classList.add("valid");
        usernameFeedback.innerHTML = '<span class="symbol">✔️</span>Username is valid.';
        usernameFeedback.className = "feedback valid";
    }
}

function validateContactNumber() {
    var contactNumberInput = document.getElementById("contactnumber");
    var contactNumber = contactNumberInput.value.trim();
    var contactNumberFeedback = document.getElementById("contactnumber_feedback");
    var validFormat = /^\d{10,11}$/;

    contactNumberInput.classList.remove("invalid", "valid");
    contactNumberFeedback.innerHTML = '';
    
    if (contactNumber === "") {
        contactNumberInput.classList.add("invalid");
        contactNumberInput.classList.remove("valid");
        contactNumberFeedback.innerHTML = '<span class="symbol">❌</span>Contact number cannot be empty.';
        contactNumberFeedback.className = "feedback invalid";
        return false;
    }

    if (!validFormat.test(contactNumber)) {
        contactNumberInput.classList.add("invalid");
        contactNumberInput.classList.remove("valid");
        contactNumberFeedback.innerHTML = '<span class="symbol">❌</span>Contact number must be 10 to 11 digits.';
        contactNumberFeedback.className = "feedback invalid";
        return false;
    } else {
        contactNumberInput.classList.remove("invalid");
        contactNumberInput.classList.add("valid");
        contactNumberFeedback.innerHTML = '<span class="symbol">✔️</span>Contact number is valid.';
        contactNumberFeedback.className = "feedback valid";
        return true;
    }
}


window.onload = function() {
    document.getElementById("username").addEventListener('input', isValidUsername);
    document.getElementById("birthdate").addEventListener('input', validateBirthdate);
    document.getElementById("contactnumber").addEventListener('input', validateContactNumber);
    document.getElementById("email").addEventListener('input', isValidEmail);
    document.getElementById("profile_picture").addEventListener('change', validateProfilePic);
};

function validateForm() {
    var username = document.getElementById("username");
    var email = document.getElementById("email");
    var birthdate = document.getElementById("birthdate");
    var contactnumber = document.getElementById("contactnumber");
    var profile_picture = document.getElementById("profile_picture");

    if (username.classList.contains("invalid") || 
        email.classList.contains("invalid") ||
        birthdate.classList.contains("invalid") ||
        contactnumber.classList.contains("invalid") ||
        profile_picture.classList.contains("invalid")) {
        alert("Please ensure all fields are filled correctly before submitting.");
        return false;
    }
    
    if (username.value.trim() === "" || 
        email.value.trim() === "" ||
        birthdate.value.trim() === "" ||
        contactnumber.value.trim() === "") {
        alert("Please fill in all required fields.");
        return false;
    }

    return true;
}

function confirmCancel() {
    if (confirm("Are you sure you want to cancel? Any unsaved changes will be lost.")) {
        window.location.href = 'adminAdmin.php';
    }
}