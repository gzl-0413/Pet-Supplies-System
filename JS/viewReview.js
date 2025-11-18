function viewProduct(product_id) {
    window.location.href = 'productPage.php?productId=' + product_id;
}

function modifyReview(rating_id) {
    window.location.href = 'modifyReview.php?id=' + rating_id;
}

function deleteReview(rating_id) {
    if (confirm('Are you sure you want to delete this review?')) {
        window.location.href = 'deleteReview.php?id=' + rating_id;
    }
}