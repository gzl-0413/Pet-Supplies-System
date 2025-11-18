var modal = document.getElementById("replyModal");
var span = document.getElementsByClassName("rclose")[0];
var prevButtons = document.querySelectorAll('.prev');
var nextButtons = document.querySelectorAll('.next');

var replyButtons = document.querySelectorAll(".edit-btn");
replyButtons.forEach(function(button) {
    button.onclick = function() {
        var reviewId = this.getAttribute('data-id');
        document.getElementById('reviewId').value = reviewId;
        modal.style.display = "none";

        // Hide the prev and next buttons
        prevButtons.forEach(button => button.style.display = 'none');
        nextButtons.forEach(button => button.style.display = 'none');
    }
});

span.onclick = function() {
    modal.style.display = "none";

    // Show the prev and next buttons
    prevButtons.forEach(button => button.style.display = 'block');
    nextButtons.forEach(button => button.style.display = 'block');
}

window.onclick = function(event) {
    if (event.target == modal) {
        modal.style.display = "none";

        // Show the prev and next buttons
        prevButtons.forEach(button => button.style.display = 'block');
        nextButtons.forEach(button => button.style.display = 'block');
    }
}

document.querySelectorAll('.edit-btn').forEach(button => {
    button.addEventListener('click', function () {
        const reviewId = this.getAttribute('data-id');
        const comment = this.closest('tr').querySelector('td:nth-child(2)').textContent;
        const reply = this.closest('tr').querySelector('td:nth-child(5)').textContent;

        document.getElementById('reviewId').value = reviewId;
        document.getElementById('commentText').textContent = comment;

        document.getElementById('replyMessage').value = reply ? reply : '';

        document.getElementById('replyModal').style.display = 'block';

        // Hide the prev and next buttons
        prevButtons.forEach(button => button.style.display = 'none');
        nextButtons.forEach(button => button.style.display = 'none');
    });
});

// Close the modal and show buttons
span.addEventListener('click', function () {
    modal.style.display = 'none';
    prevButtons.forEach(button => button.style.display = 'block');
    nextButtons.forEach(button => button.style.display = 'block');
});

// For handling the reply message
document.addEventListener('DOMContentLoaded', function() {
    const replyMessage = document.getElementById('replyMessage');
    const sendReplyBtn = document.querySelector('.send-reply-btn');

    function toggleReplyButton() {
        if (replyMessage.value.trim() === "") {
            sendReplyBtn.disabled = true;
            sendReplyBtn.style.backgroundColor = 'grey';
        } else {
            sendReplyBtn.disabled = false;
            sendReplyBtn.style.backgroundColor = '';
        }
    }

    replyMessage.addEventListener('input', toggleReplyButton);
    toggleReplyButton();
});

let currentSlideIndex = {};

function changeSlide(direction, reviewId) {
    const slider = document.querySelector(`#slider-container-${reviewId}`);
    const slides = slider.children;
    const totalSlides = slides.length;

    if (currentSlideIndex[reviewId] === undefined) {
        currentSlideIndex[reviewId] = 0;
    }

    currentSlideIndex[reviewId] += direction;

    if (currentSlideIndex[reviewId] < 0) {
        currentSlideIndex[reviewId] = totalSlides - 1;
    } else if (currentSlideIndex[reviewId] >= totalSlides) {
        currentSlideIndex[reviewId] = 0;
    }

    const offset = -currentSlideIndex[reviewId] * 100;
    slider.style.transform = `translateX(${offset}%)`;
}

function openModal(src) {
    const modal = document.getElementById('photoModal');
    const modalImg = document.getElementById('modalImage');
    modal.style.display = 'block';
    modalImg.src = src;
}


function confirmDelete(url) {
    let confirmAction = confirm("Are you sure you want to delete this review?");
    
    if (confirmAction) {
        window.location.href = url;
    }
}
 

document.addEventListener('DOMContentLoaded', function () {
    function closeModal() {
        const modal = document.getElementById('photoModal');
        modal.style.display = 'none';
    }

    document.querySelector('.pclose').addEventListener('click', closeModal);
});

document.getElementById('selectAll').addEventListener('click', function () {
    const checkboxes = document.querySelectorAll('.review-checkbox');
    checkboxes.forEach(checkbox => checkbox.checked = this.checked);
});

function toggleSelectAll(source) {
    const checkboxes = document.querySelectorAll('.review-checkbox');
    checkboxes.forEach(checkbox => checkbox.checked = source.checked);
}

// Handle Reply button functionality
document.addEventListener('DOMContentLoaded', function () {
    // Add event listener for the Reply buttons
    const replyButtons = document.querySelectorAll('.edit-btn');
    replyButtons.forEach(button => {
        button.addEventListener('click', function (event) {
            event.preventDefault(); // Prevent the default action

            // Retrieve the ID of the review to be replied to
            const reviewId = this.getAttribute('data-id');
            const commentText = this.closest('tr').querySelector('td:nth-child(3)').innerText;

            // Display the modal and fill in the values
            document.getElementById('replyModal').style.display = 'block';
            document.getElementById('reviewId').value = reviewId;
            document.getElementById('commentText').innerText = commentText;
        });
    });

    // Close modal
    document.querySelector('.rclose').addEventListener('click', function () {
        document.getElementById('replyModal').style.display = 'none';
    });

    // Close modal when clicking outside of it
    window.addEventListener('click', function (event) {
        if (event.target == document.getElementById('replyModal')) {
            document.getElementById('replyModal').style.display = 'none';
        }
    });
});