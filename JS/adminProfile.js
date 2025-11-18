document.addEventListener('DOMContentLoaded', function () {
    const viewProfileBtn = document.getElementById('viewProfileBtn');
    if (viewProfileBtn) {
        viewProfileBtn.addEventListener('click', function (e) {
            e.preventDefault();
            displayAdminInformation();
        });
    }
});

function displayAdminInformation() {
    var modal = document.getElementById('profileModal');
    var content = document.getElementById('profileContent');
    var closeBtn = modal.querySelector('.close');
    var navItems = modal.querySelectorAll('.nav-item');
    var prevButtons = document.querySelectorAll('.prev');
var nextButtons = document.querySelectorAll('.next');

    function loadContent(target) {
        let url;
        if (target === 'profileInfo') {
            url = 'adminProfile.php';
        } else if (target === 'changePassword') {
            url = 'adminChangePassword.php';
        }
        fetch(url)
            .then(response => response.text())
            .then(data => {
                content.innerHTML = data;
                if (target === 'profileInfo') {
                    editAdminProfile();
                }
                else if (target === 'changePassword') {
                    changeAdminPassword();
                }
            })
            .catch(error => console.error('Fetch error:', error));
    }

    function showModal() {
        modal.style.display = 'block';
        prevButtons.forEach(button => button.style.display = 'none');
        nextButtons.forEach(button => button.style.display = 'none');
        setTimeout(() => {
            modal.classList.add('show');
        }, 10);
    }

    function closeModal() {
        modal.classList.remove('show');
        setTimeout(() => {
            modal.style.display = 'none';
        }, 300);
    }

    // Initial load of profile information
    loadContent('profileInfo');
    showModal();

    // Navigation event listeners
    navItems.forEach(item => {
        item.addEventListener('click', function () {
            navItems.forEach(nav => nav.classList.remove('active'));
            this.classList.add('active');
            loadContent(this.dataset.target);
        });
    });

    if (closeBtn) {
        closeBtn.addEventListener('click', closeModal);
    }

    // Close modal when clicking outside
    window.addEventListener('click', function (event) {
        if (event.target == modal) {
            closeModal();
        }
    });
}

function editAdminProfile() {
    const editBtn = document.getElementById('editProfileBtn');
    const saveBtn = document.getElementById('saveProfileBtn');
    const editableFields = document.querySelectorAll('.editable');
    const changeProfilePicBtn = document.getElementById('changeProfilePicBtn');
    const profilePicInput = document.getElementById('profilePicInput');
    const profilePicture = document.querySelector('.profile-picture');

    if (editBtn) {
        editBtn.addEventListener('click', function () {
            console.log('Edit button clicked');
            editableFields.forEach(field => {
                field.contentEditable = true;
                field.style.backgroundColor = '#f0f0f0';
                field.style.padding = '2px 5px';
                field.style.borderRadius = '3px';
                field.style.border = '1px solid #ccc';
            });
            changeProfilePicBtn.style.display = 'block';
            editBtn.style.display = 'none';
            saveBtn.style.display = 'inline-block';
        });
    }

    changeProfilePicBtn.addEventListener('click', function () {
        profilePicInput.click();
    });

    profilePicInput.addEventListener('change', function (e) {
        if (e.target.files && e.target.files[0]) {
            const reader = new FileReader();
            reader.onload = function (e) {
                profilePicture.src = e.target.result;
            };
            reader.readAsDataURL(e.target.files[0]);
        }
    });

    saveBtn.addEventListener('click', function () {
        const formData = new FormData();
        editableFields.forEach(field => {
            formData.append(field.id, field.textContent.trim());
        });
        if (profilePicInput.files[0]) {
            formData.append('profilepic', profilePicInput.files[0]);
        }

        fetch('adminUpdateProfile.php', {
            method: 'POST',
            body: formData,
        })
            .then(response => response.json())
            .then(result => {
                if (result.success) {
                    editableFields.forEach(field => {
                        field.contentEditable = false;
                        field.style.backgroundColor = '';
                        field.style.padding = '';
                        field.style.borderRadius = '';
                    });
                    changeProfilePicBtn.style.display = 'none';
                    editBtn.style.display = 'inline-block';
                    saveBtn.style.display = 'none';
                    profilePicture.src = `../uploads/${result.newPhoto}`;
                    alert('Profile updated successfully!');
                    window.location.reload();
                } else {
                    alert(result.message || 'Failed to update profile');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred. Please try again.');
            });
    });
}

function changeAdminPassword() {
    const changePasswordForm = document.querySelector('.change-password-form');

    const toggles = document.querySelectorAll('.toggle-password');
    toggles.forEach(toggle => {
        toggle.addEventListener('click', function () {
            const targetId = this.dataset.target;
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

    if (changePasswordForm) {
        changePasswordForm.addEventListener('submit', function (e) {
            e.preventDefault();

            const formData = new FormData(this);

            fetch('adminChangePassword.php', {
                method: 'POST',
                body: formData
            })
                .then(response => response.json())
                .then(result => {
                    if (result.success) {
                        alert(result.message);
                        changePasswordForm.reset();
                    } else {
                        alert(result.message || 'Failed to change password');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred. Please try again.');
                });
        });
    }
}

document.addEventListener('DOMContentLoaded', function() {
    const viewProfileBtn = document.getElementById('viewProfileBtn');
    if (viewProfileBtn) {
        viewProfileBtn.addEventListener('click', displayAdminInformation);
    }
});