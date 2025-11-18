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
        usernameFeedback.innerHTML = '<span class="symbol">✔️</span>Admin name is valid.';
        usernameFeedback.className = "feedback valid";
    }
}

function isValidEmail() {
    var emailInput = document.getElementById("email");
    var emailFeedback = document.getElementById("email_feedback");
    var email = emailInput.value.trim();
    var re = /^[^\s@]+@[^\s@]+\.(com|org|net|edu|gov)$/;

    if (email === "") {
        emailInput.classList.add("invalid");
        emailInput.classList.remove("valid");
        emailFeedback.innerHTML = '<span class="symbol">❌</span>This field cannot be empty.';
        emailFeedback.className = "feedback invalid";
    } else if (!re.test(email)) {
        emailInput.classList.add("invalid");
        emailInput.classList.remove("valid");
        emailFeedback.innerHTML = '<span class="symbol">❌</span>Please enter a valid email format.';
        emailFeedback.className = "feedback invalid";
    } else {
        emailInput.classList.remove("invalid");
        emailInput.classList.add("valid");
        emailFeedback.innerHTML = '<span class="symbol">✔️</span>Email is valid.';
        emailFeedback.className = "feedback valid";
    }
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

    if (file) {
        feedback.classList.add("valid");
        feedback.classList.remove("invalid");
        feedback.innerHTML = '<span class="symbol">✔️</span>Valid image selected.';
        feedback.className = "feedback valid";
        return;
    }
}

window.onload = function() {
    document.getElementById("username").addEventListener('input', isValidUsername);
    document.getElementById("email").addEventListener('input', isValidEmail);
    document.getElementById("profile_picture").addEventListener('change', validateProfilePic);
};

function validateForm() {
    var username = document.getElementById("username");
    var email = document.getElementById("email");
    var profilePic = document.getElementById("profile_picture");

    if (username.classList.contains("invalid") || 
        email.classList.contains("invalid") ||
        profilePic.classList.contains("invalid")) {
        alert("Please ensure all fields are filled correctly before submitting.");
        return false;
    }
    
    if (username.value.trim() === "" || 
        email.value.trim() === "") {
        alert("Please fill in all required fields.");
        return false;
    }

    return true;
}

function previewImage(event) {
    const file = event.target.files[0];
    const reader = new FileReader();
    const newImage = document.getElementById('new-profile-pic');
    const currentImage = document.getElementById('current-profile-pic');

    if (file) {
        reader.onload = function() {
            newImage.src = reader.result;
            newImage.style.display = 'block';

            if (currentImage) {
                currentImage.style.display = 'none';
            }
        };
        reader.readAsDataURL(file);
    } else {
        newImage.style.display = 'none';
    }
}

function confirmCancel() {
    if (confirm("Are you sure you want to cancel? Any unsaved changes will be lost.")) {
        window.location.href = 'adminAdmin.php';
    }
}