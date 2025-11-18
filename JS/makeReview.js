function confirmSubmit() {
    const rating = document.querySelector('input[name="rating"]:checked');
    const review = document.getElementById('review').value.trim();

    if (!rating) {
        alert("Please select a star rating before submitting your review.");
        return false; // Prevent form submission
    }

    return true; // Allow form submission
}

function confirmCancel() {
    return confirm("Are you sure you want to cancel?");
}

document.getElementById('photoInput').addEventListener('change', function(event) {
    const previewContainer = document.getElementById('imagePreview');
    previewContainer.innerHTML = ''; // Clear previous previews

    const files = event.target.files;

    if (files.length > 3) {
        alert('You can only upload a maximum of 3 images.');
        event.target.value = '';
        return;
    }

    for (let i = 0; i < files.length; i++) {
        const file = files[i];
        const reader = new FileReader();

        reader.onload = function(e) {
            const img = document.createElement('img');
            img.src = e.target.result;
            previewContainer.appendChild(img);
        }

        reader.readAsDataURL(file);
    }
});