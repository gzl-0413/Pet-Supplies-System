document.getElementById('ratingFilter').addEventListener('change', function() {
    
    const selectedRating = this.value;

    const url = new URL(window.location.href);

    url.searchParams.set('rating', selectedRating);
    url.searchParams.set('productId', productId);

    console.log("New URL:", url.toString());

    window.location.href = url.toString();
});
