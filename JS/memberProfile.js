document.addEventListener('DOMContentLoaded', function () {
    const viewProfileBtn = document.getElementById('viewProfileBtn');
  

    if (viewProfileBtn) {
        viewProfileBtn.addEventListener('click', function (e) {
            e.preventDefault();
            displayInformation('profileInfo'); // Load Profile Information by default
        });
    }
 
    
});



function displayInformation(target) {
    var modal = document.getElementById('profileModal');
    var content = document.getElementById('profileContent');
    var closeBtn = modal.querySelector('.close');
    var navItems = modal.querySelectorAll('.nav-item');
    
    function loadContent(target) {
        let url;
        if (target === 'profileInfo') {
            url = 'memberProfile.php';
        } else if (target === 'changePassword') {
            url = 'changePassword.php';
        }else if (target === 'order') {
            url = 'userOrder.php';
        }else if (target === 'wishlist') {
            url = 'wishlist.php';
        }else {
            url = 'defaultPage.php';
        }
        fetch(url)
            .then(response => response.text())
            .then(data => {
                content.innerHTML = data;
                if (target === 'profileInfo') {
                    editProfile();
                }
                else if (target === 'changePassword') {
                    changePassword();
                } else if (target === 'order') {
                    order();
                }else if (target === 'wishlist') {
                    wishlist();
                }
            })
            .catch(error => console.error('Fetch error:', error));

    }

    function showModal() {
        modal.style.display = 'block';
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
    loadContent(target);
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


function editProfile() {
    const editBtn = document.getElementById('editProfileBtn');
    const saveBtn = document.getElementById('saveProfileBtn');
    const editableFields = document.querySelectorAll('.editable');
    const changeProfilePicBtn = document.getElementById('changeProfilePicBtn');
    const profilePicInput = document.getElementById('profilePicInput');
    const profilePicture = document.querySelector('.profile-picture');

    editBtn.addEventListener('click', function () {
        console.log('Edit button clicked');
        editableFields.forEach(field => {

            field.contentEditable = true;
            field.style.width = '100%';
            field.style.backgroundColor = '#f0f0f0';
            field.style.padding = '2px 5px';
            field.style.borderRadius = '3px';
            field.style.border = '1px solid #ccc';


            changeProfilePicBtn.style.display = 'block';
            editBtn.style.display = 'none';
            saveBtn.style.display = 'inline-block';
        });
    });

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
        console.log('Save button clicked');
        const formData = new FormData();
        editableFields.forEach(field => {
            formData.append(field.id, field.textContent.trim());
        });
        if (profilePicInput.files[0]) {
            formData.append('profilepic', profilePicInput.files[0]);
        }

        fetch('updateProfile.php', {
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
                    location.reload();
                } else {
                    let errorMessage = 'Failed to update profile:\n\n';
                    if (result.errors) {
                        Object.entries(result.errors).forEach(([field, error]) => {
                            errorMessage += `${field}: ${error}\n`;
                        });
                    } else {
                        errorMessage += result.message || 'Unknown error occurred';
                    }
                    alert(errorMessage);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred. Please try again.');
            });
    });
}
document.addEventListener('DOMContentLoaded', editProfile);


function changePassword() {
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

            fetch('changePassword.php', {
                method: 'POST',
                body: formData
            })
                .then(response => response.json())
                .then(result => {
                    if (result.success) {
                        alert(result.message);
                        changePasswordForm.reset();
                    } else {
                        let errorMessage = 'Failed to change password:\n\n';
                        if (result.errors) {
                            Object.entries(result.errors).forEach(([field, error]) => {
                                errorMessage += `${field}: ${error}\n`;
                            });
                        } else {
                            errorMessage += result.message || 'Unknown error occurred';
                        }
                        alert(errorMessage);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred. Please try again.');
                });
        });
    }
}

document.addEventListener('DOMContentLoaded', changePassword);
function order() {
    const tabLinks = document.querySelectorAll('.tablinks');
    const tabContents = document.querySelectorAll('.tab-content');

    // Event listener for tab switching
    tabLinks.forEach(link => {
        link.addEventListener('click', function (event) {
            // Remove active class from all tab links
            tabLinks.forEach(link => link.classList.remove('active'));

            // Hide all tab contents
            tabContents.forEach(content => content.classList.remove('active'));
            tabContents.forEach(content => content.style.display = 'none');

            // Activate the clicked tab
            const tabName = this.getAttribute('onclick').split("'")[1]; // Extract the tab name from 'onclick' attribute
            document.getElementById(tabName).style.display = 'block';
            document.getElementById(tabName).classList.add('active');
            this.classList.add('active');
        });
    });

    // Event listener for the update buttons to mark orders as 'Completed'
    const updateButtons = document.querySelectorAll('.update-button');
    updateButtons.forEach(button => {
        button.addEventListener('click', function (event) {
            event.preventDefault(); // Prevent the default link behavior

            const confirmed = confirm("Are you sure you want to mark this order as 'Completed'?");
            if (confirmed) {
                const orderId = this.getAttribute('data-orderId');
                const productId = this.getAttribute('data-productId');
                window.location.href = `orderReceived.php?orderId=${orderId}&productId=${productId}`;
            }
        });
    });

    // Event listener for the cancel buttons
    const cancelButtons = document.querySelectorAll('.cancel-button');
    cancelButtons.forEach(button => {
        button.addEventListener('click', function (event) {
            event.preventDefault(); // Prevent the default link behavior

            const confirmed = confirm("Are you sure you want to cancel this order?");
            if (confirmed) {
                const reason = prompt("Please provide a reason for cancellation:");
                if (reason) {
                    const orderId = this.getAttribute('data-orderId');
                    const productId = this.getAttribute('data-productId');

                    window.location.href = `cancelOrder.php?orderId=${orderId}&productId=${productId}&reason=${encodeURIComponent(reason)}`;
                }
            }
        });
    });

    // Event listener for the "View Details" buttons
    const viewDetailLinks = document.querySelectorAll('.view-details');
    viewDetailLinks.forEach(link => {
        link.addEventListener('click', function (event) {
            event.preventDefault(); // Prevent default action
            const orderId = this.getAttribute('data-orderId');
            fetchOrderDetails(orderId);
        });
    });
}

// Ensure this function is called once the DOM is fully loaded
document.addEventListener('DOMContentLoaded', order);

// Function to fetch and display order details in the modal
function fetchOrderDetails(orderId) {
    const content = document.getElementById('profileContent');

    fetch(`getOrderDetails.php?orderId=${orderId}`)
        .then(response => response.text())
        .then(data => {
            content.innerHTML = data; // Display the order details in the modal content

            // Display the modal if hidden
            document.getElementById('profileModal').style.display = 'block';
        })
        .catch(error => console.error('Error fetching order details:', error));
}

// Function to close order details and return to the main order list
function closeOrderDetails() {
    displayInformation('order'); // Load the main order list back into the modal
}


function wishlist() {
    const wishlistContainer = document.getElementById('wishlistContainer');

    // Attach event listener for remove buttons
    function attachRemoveListeners() {
        const removeButtons = document.querySelectorAll('.remove-btn');

        removeButtons.forEach(button => {
            button.addEventListener('click', function (e) {
                e.preventDefault(); // Prevent form submission/refresh

                const productId = this.closest('form').querySelector('input[name="productId"]').value;

                // Call the confirmation prompt before removing the item
                const confirmed = confirm('Are you sure you want to remove this item from your wishlist?');
                if (confirmed) {
                    // Call the removeItem function to handle product removal if confirmed
                    removeItemFromWishlist(productId);
                } else {
                    alert('Item not removed.');
                }
            });
        });
    }
    function attachRowClickListeners() {
        const wishlistRows = document.querySelectorAll('.wishlist-item');

        wishlistRows.forEach(row => {
            row.addEventListener('click', function (e) {
                const target = e.target;

                // Check if the clicked element is not the remove button or its children
                if (!target.closest('.remove-btn') && !target.closest('form')) {
                    const productId = this.dataset.productId;
                    window.location.href = `productPage.php?productId=${productId}`;
                }
            });
        });
    }
    // Function to remove item from the wishlist using AJAX
    function removeItemFromWishlist(productId) {
        const formData = new FormData();
        formData.append('productId', productId);

        // Send AJAX request to remove the item
        fetch('wishlist.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.text())
        .then(data => {
            const newContent = document.createElement('div');
            newContent.innerHTML = data;
            const newWishlistItems = newContent.querySelector('.wishlist-container');

            // Replace only the wishlist section with the new data
            wishlistContainer.innerHTML = newWishlistItems.innerHTML;

            // Reattach the event listeners to the new remove buttons
            attachRemoveListeners();
        })
        .catch(error => {
            console.error('Error removing item:', error);
            alert('Failed to remove item. Please try again.');
        });
    }

    // Load the initial wishlist and set up event listeners
    fetch('wishlist.php')
        .then(response => response.text())
        .then(data => {
            const newContent = document.createElement('div');
            newContent.innerHTML = data;
            const wishlistItems = newContent.querySelector('.wishlist-container');
            wishlistContainer.innerHTML = wishlistItems.innerHTML;

            // Attach event listeners to the remove buttons
            attachRemoveListeners();
        })
        .catch(error => {
            console.error('Error fetching wishlist:', error);
        });
}

document.addEventListener('DOMContentLoaded', wishlist);

// function paymentHistory() {
//     // Select modal elements
//     const modal = document.getElementById('payment-modal');
//     const closeModal = modal?.querySelector('.close');
//     const modalBody = modal?.querySelector('.modal-body');

//     if (!modal || !closeModal || !modalBody) {
//         console.error('Modal elements not found');
//         return;
//     }

//     // Close the modal when the "x" is clicked
//     closeModal.addEventListener('click', function() {
//         modal.style.display = 'none';
//     });

//     // Delegate click events for "Show Details" buttons
//     document.querySelectorAll('.show-details-button').forEach(button => {
//         button.addEventListener('click', function() {
//             const orderId = button.getAttribute('data-order-id');
//             if (!orderId) {
//                 console.error('Order ID not found');
//                 return;
//             }

//             // Show the modal
//             modal.style.display = 'block';

//             // Display a loading message
//             modalBody.innerHTML = '<div class="loading">Loading payment details...</div>';

//             // Fetch order details using AJAX
//             fetch(`fetchOrderDetails.php?orderId=${encodeURIComponent(orderId)}`)
//                 .then(response => response.text())
//                 .then(data => {
//                     // Replace the loading message with the fetched details
//                     modalBody.innerHTML = data;
//                 })
//                 .catch(error => {
//                     modalBody.innerHTML = '<div class="error">Failed to load payment details.</div>';
//                     console.error('Error fetching order details:', error);
//                 });
//         });
//     });

//     // Close the modal when clicking outside of it
//     window.addEventListener('click', function(event) {
//         if (event.target === modal) {
//             modal.style.display = 'none';
//         }
//     });
// }

// // Initialize the paymentHistory function once the DOM is fully loaded
// document.addEventListener('DOMContentLoaded', function() {
//     paymentHistory();
// });
