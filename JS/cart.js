


function deleteFromCart(button) {
    
    
    event.preventDefault();
// Ask for confirmation before proceeding
if (!confirm('Are you sure you want to remove this item from your cart?')) {
    // If the user cancels the confirmation dialog, return early and do nothing
    return;
}

const url = button.getAttribute('data-post');

fetch(url, {
    method: 'POST',
})
.then(response => response.text())
.then(data => {
    // Log the response (optional for debugging)
    console.log(data);
    
    // Optionally, reload the page to reflect the changes
    location.reload();
})
.catch(error => console.error('Error:', error));
}

function removeItem() {
        alert('Item removed from cart!');
        // Logic to remove item from the cart should be implemented here
    }


function createHiddenInput(name, value) {
    const input = document.createElement('input');
    input.type = 'hidden';
    input.name = name;
    input.value = value;
    return input;
}

function submitCheckoutForm() {
const itemCount = parseInt(document.getElementById('item-count').textContent, 10);

if (itemCount === 0) {
    alert('Please select at least 1 product before proceeding to checkout.');
} else {
    document.getElementById('checkout-form').submit();
}
}


function toggleAllCheckboxes(mainCheckbox) {
    const itemCheckboxes = document.querySelectorAll('.cart-item-checkbox');
    itemCheckboxes.forEach(function(checkbox) {
        // Only toggle if the checkbox is not disabled (i.e., the product is not hidden)
        if (!checkbox.disabled) {
            checkbox.checked = mainCheckbox.checked;
        }
    });
    updateTotal();
}
 // Update the total cart price and item count
 function updateTotal() {
    let total = 0;
    let itemCount = 0;

    const cartItems = document.querySelectorAll('.cart-item');
    const form = document.getElementById('checkout-form');
    const existingInputs = form.querySelectorAll('input[type="hidden"]');
    existingInputs.forEach(input => input.remove());


    cartItems.forEach(item => {
        const checkbox = item.querySelector('.cart-item-checkbox');
        const price = parseFloat(item.querySelector('.cart-item-total').getAttribute('data-price'));
        const quantityInput = item.querySelector('.quantity-input');
        const quantity = parseInt(quantityInput.value, 10);
        const subtotal = price * quantity;

        const subtotalElement = item.querySelector('.cart-item-total');
        subtotalElement.textContent = `RM${subtotal.toFixed(2)}`;
        subtotalElement.setAttribute('data-quantity', quantity);

        if (checkbox.checked) {
            total += subtotal;
            itemCount++;

            const productId = checkbox.getAttribute('data-product-id');
            const productName = item.querySelector('.cart-item-details p').textContent;
            const cartId = item.querySelector('.cart-item-actions button').getAttribute('data-post').split('=')[1];
            form.appendChild(createHiddenInput('product_ids[]', productId));
            form.appendChild(createHiddenInput('product_names[]', productName));
            form.appendChild(createHiddenInput('quantities[]', quantity));  // Updated quantity
            form.appendChild(createHiddenInput('prices[]', price));
            form.appendChild(createHiddenInput('subtotals[]', subtotal));  // Updated subtotal
            form.appendChild(createHiddenInput('cart_ids[]', cartId));

        }
    });

    document.getElementById('total-price').textContent = total.toFixed(2);
    document.getElementById('item-count').textContent = itemCount;
}

 


document.addEventListener('DOMContentLoaded', function() {
    // Initialize variables for handling quantity controls
    const decrementButtons = document.querySelectorAll('.decrement-btn');
    const incrementButtons = document.querySelectorAll('.increment-btn');
    const quantityInputs = document.querySelectorAll('.quantity-input');

    // Loop through each product's input group to set up event listeners
    document.querySelectorAll('.input-group').forEach(function(group) {
        const decrementBtn = group.querySelector('.decrement-btn');
        const incrementBtn = group.querySelector('.increment-btn');
        const quantityInput = group.querySelector('.quantity-input');
        const maxQuantity = parseInt(quantityInput.getAttribute('max'));
        const stockMessage = group.querySelector('.stock-message');
        const prodId = group.querySelector('.prod').value;

        // Decrement quantity
        decrementBtn.addEventListener('click', function(event) {
            event.preventDefault();  // Prevent any default action
            let currentQuantity = parseInt(quantityInput.value);

            if (currentQuantity > 1) {
                quantityInput.value = currentQuantity - 1;
                stockMessage.style.display = 'none'; // Hide the stock message
                updateQuantityInCart(prodId, quantityInput.value);
            }
        });

        // Increment quantity
        incrementBtn.addEventListener('click', function(event) {
            event.preventDefault();  // Prevent any default action
            let currentQuantity = parseInt(quantityInput.value);

            if (currentQuantity < maxQuantity) {
                quantityInput.value = currentQuantity + 1;
                if (currentQuantity + 1 === maxQuantity) {
                    stockMessage.style.display = 'inline'; // Show the stock message
                }
                updateQuantityInCart(prodId, quantityInput.value);
            } else {
                stockMessage.style.display = 'inline'; // Show the stock message if at the limit
            }
        });
    });

   
    function updateQuantityInCart(productId, newQuantity) {
        $.ajax({
            method: "POST",
            url: "handlecart.php",
            data: {
                "prod_id": productId,
                "prod_qty": newQuantity,
                "scope": "update"
            },
            success: function(response) {
                console.log(response); // Log response for debugging
                updateTotal(); // Update total after quantity change
            },
            error: function(xhr, status, error) {
                console.error("AJAX request failed:", status, error);
                alert("Failed to update quantity: " + error); // Show error message to user
            }
        });
    }
});
