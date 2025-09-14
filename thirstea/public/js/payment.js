document.addEventListener('DOMContentLoaded', () => {
  const user = JSON.parse(localStorage.getItem('user'));
  let cart = JSON.parse(localStorage.getItem('cart')) || [];
  const deliveryFee = 40;
  const customerNameElement = document.getElementById('customer-name');

  // Construct full customer name or fallback
  const fullName = user ? `${user.firstName || ''} ${user.lastName || ''}`.trim() || user.username || 'Guest' : 'Guest';

  if (customerNameElement && user) {
    customerNameElement.textContent = `Customer Name: ${fullName}`;
  }

  const orderSummaryElement = document.querySelector('.order-summary');
  if (!orderSummaryElement) return;

  if (cart.length === 0) {
    orderSummaryElement.innerHTML = '<p>Your cart is empty.</p>';
    return;
  }

  let total = 0;

  // Calculate total excluding delivery fee first
  cart.forEach(item => {
    const quantity = item.quantity || 1;
    const sizePrice = parseFloat(item.sizePrice);

    if (isNaN(sizePrice) || sizePrice <= 0) {
      console.warn(`Invalid size price for product ID ${item.productId || item.MenuItemID}`);
      return;
    }

    total += sizePrice * quantity;
  });

  // Add delivery fee once after summing all items
  total += deliveryFee;

  // Render order items
  cart.forEach(item => {
    const quantity = item.quantity || 1;
    const sizeLabel = item.sizeLabel || '';
    const sizePrice = parseFloat(item.sizePrice);

    if (isNaN(sizePrice) || sizePrice <= 0) return;

    const displayName = sizeLabel ? `${item.name || 'Product'} ${sizeLabel}` : (item.name || 'Product');
    const itemTotal = sizePrice * quantity;

    orderSummaryElement.innerHTML += `
      <div class="order-item">
        <p><strong>${displayName}</strong> - ₱${sizePrice.toFixed(2)} x ${quantity}</p>
        <p>₱${itemTotal.toFixed(2)}</p>
      </div>
    `;
  });

  // Render delivery fee and total
  orderSummaryElement.innerHTML += `
    <div class="total">
      <p><strong>Delivery Fee: ₱${deliveryFee.toFixed(2)}</strong></p>
      <p><strong>Total: ₱${total.toFixed(2)}</strong></p>
    </div>
  `;

  const placeOrderButton = document.querySelector('.place-order');
  if (!placeOrderButton) return;

  placeOrderButton.addEventListener('click', async () => {
    const addressInput = document.getElementById('customer-address');
    const address = addressInput ? addressInput.value.trim() : '';
    const paymentMethodInput = document.querySelector('input[name="payment"]:checked');
    const paymentMethod = paymentMethodInput ? paymentMethodInput.value : null;

    if (!address) {
      alert('Please enter a delivery address.');
      if (addressInput) addressInput.focus();
      return;
    }

    if (!paymentMethod) {
      alert('Please select a payment method.');
      return;
    }

    if (!user || !user.userId) {
      alert('User not logged in. Please login to place an order.');
      window.location.href = '/login.html'; // Adjust login URL as needed
      return;
    }

    if (cart.length === 0) {
      alert('Your cart is empty or contains invalid items.');
      return;
    }

    // Prepare order details payload with customerName included
    const orderDetails = {
      customerId: user.userId,
      customerName: fullName,
      address: address,
      paymentMethod: paymentMethod,
      cart: cart.map(item => ({
        productId: item.productId || item.MenuItemID,
        quantity: item.quantity,
        sizeLabel: item.sizeLabel || '',
        sizePrice: parseFloat(item.sizePrice),
        name: item.name || ''
      }))
    };

    try {
      const response = await fetch('http://localhost:3000/thirstea/backend/placeOrder.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        credentials: 'include', // if backend uses sessions
        body: JSON.stringify(orderDetails)
      });

      const data = await response.json();

      if (response.ok && data.success) {
        localStorage.removeItem('cart');
        localStorage.setItem('orderTotal', total.toFixed(2)); // Save total for confirmation page
        alert('Order placed successfully!');
        window.location.href = '../html/confirmation.html'; // Adjust confirmation page URL as needed
      } else {
        alert('Failed to place order: ' + (data.message || 'Unknown error'));
      }
    } catch (error) {
      console.error('Order placement error:', error);
      alert('An error occurred while placing your order. Please try again later.');
    }
  });
});
