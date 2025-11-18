const stars = document.querySelectorAll('.star');
const hiddenRateInput = document.getElementById('hidden-rate');
const starRatingDiv = document.getElementById('star-rating');

stars.forEach(star => {
    star.addEventListener('mouseover', () => {
        const value = star.getAttribute('data-value');
        fillStars(value);
    });

    star.addEventListener('mouseout', () => {
        fillStars(hiddenRateInput.value);
    });

    star.addEventListener('click', () => {
        hiddenRateInput.value = star.getAttribute('data-value');
        fillStars(hiddenRateInput.value);
    });
});

function fillStars(value) {
    stars.forEach(star => {
        if (star.getAttribute('data-value') <= value) {
            star.classList.add('filled');
        } else {
            star.classList.remove('filled');
        }
    });
}

function confirmUpdate() {
    return confirm("Are you sure you want to update the review?");
}

function confirmCancel() {
    const confirmed = confirm("Are you sure you want to cancel? Unsaved changes will be lost.");
    if (confirmed) {
        window.location.href = 'viewReview.php';
    }
}

function previewImages(event) {
    const newPhotosPreview = document.querySelector('.new-photos-preview');
    newPhotosPreview.innerHTML = '';

    const files = event.target.files;
    for (let i = 0; i < files.length; i++) {
        const file = files[i];
        const reader = new FileReader();
        
        reader.onload = function(e) {
            const img = document.createElement('img');
            img.src = e.target.result;
            img.style.maxWidth = '150px';
            img.style.margin = '5px';
            newPhotosPreview.appendChild(img);
        };
        
        reader.readAsDataURL(file);
    }
}