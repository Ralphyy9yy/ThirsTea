document.addEventListener('DOMContentLoaded', () => {
    // Add functionality for the buttons
    const addToCartButton = document.querySelector('.add-to-cart');
    const orderNowButton = document.querySelector('.order-now');

    addToCartButton.addEventListener('click', () => {
        alert('Item added to cart!');
    });

    orderNowButton.addEventListener('click', () => {
        alert('Order placed successfully!');
    });
});
