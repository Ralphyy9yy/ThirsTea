document.addEventListener('DOMContentLoaded', () => {
    const container = document.querySelector('.items-container');
    const subtotalSpan = document.querySelectorAll('.summary .total span')[1];
    const deliverySpan = document.querySelectorAll('.summary .total span')[3];
    const checkoutFinalButton = document.querySelector('.checkout-final');
    const totalPayDiv = document.querySelector('.checkout-button');
  
    const DELIVERY_FEE = 40;
    let cart = JSON.parse(localStorage.getItem('cart')) || [];
  
    if (!container) return;
  
    if (cart.length === 0) {
      container.innerHTML = '<p>Your cart is empty.</p>';
      updateTotals(0);
      return;
    }
  
    async function renderBasket() {
      container.innerHTML = '';
      let subtotal = 0;
  
      const items = await Promise.all(
        cart.map(async (item) => {
          try {
            const productId = item.MenuItemID || item.productId;
            const res = await fetch(`http://localhost:3000/thirstea/backend/products.php?id=${productId}`);
            const data = await res.json();
            return { product: data[0], quantity: item.quantity, sizePrice: item.sizePrice, name: item.name };
          } catch (err) {
            console.error(err);
            return null;
          }
        })
      );
  
      items.forEach((item, index) => {
        if (!item || !item.product) return;
        const product = item.product;
        const quantity = item.quantity;
  
        // Use sizePrice from cart item if available, else fallback to base price
        const price = item.sizePrice ? parseFloat(item.sizePrice) : parseFloat(product.ItemPrice);
        if (isNaN(price) || price <= 0) {
          console.warn(`Invalid price for product ID ${product.MenuItemID}`);
          return;
        }
  
        const sizeLabel = item.sizeLabel || '';
        const displayName = sizeLabel ? `${item.name || product.ItemName} (${sizeLabel})` : (item.name || product.ItemName);
  
        const itemTotal = price * quantity;
        subtotal += itemTotal;
  
        const itemDiv = document.createElement('div');
        itemDiv.className = 'item';
  
        const productInfo = document.createElement('div');
        productInfo.innerHTML = `<p><strong>${displayName}</strong></p>`;
  
        const quantityControls = document.createElement('div');
        quantityControls.className = 'quantity-controls';
  
        const decreaseBtn = document.createElement('button');
        decreaseBtn.textContent = '-';
        decreaseBtn.className = 'quantity-btn decrease';
  
        const quantityDisplay = document.createElement('span');
        quantityDisplay.textContent = quantity;
        quantityDisplay.className = 'quantity-display';
  
        const increaseBtn = document.createElement('button');
        increaseBtn.textContent = '+';
        increaseBtn.className = 'quantity-btn increase';
  
        quantityControls.appendChild(decreaseBtn);
        quantityControls.appendChild(quantityDisplay);
        quantityControls.appendChild(increaseBtn);
  
        const priceDiv = document.createElement('div');
        priceDiv.className = 'price-display';
        priceDiv.textContent = `₱${itemTotal.toFixed(2)}`;
  
        const removeBtn = document.createElement('button');
        removeBtn.textContent = 'Remove';
        removeBtn.className = 'remove-btn';
  
        itemDiv.appendChild(productInfo);
        itemDiv.appendChild(quantityControls);
        itemDiv.appendChild(priceDiv);
        itemDiv.appendChild(removeBtn);
  
        container.appendChild(itemDiv);
  
        decreaseBtn.addEventListener('click', () => {
          if (cart[index].quantity > 1) {
            cart[index].quantity--;
            saveAndRender();
          }
        });
  
        increaseBtn.addEventListener('click', () => {
          cart[index].quantity++;
          saveAndRender();
        });
  
        removeBtn.addEventListener('click', () => {
          cart.splice(index, 1);
          saveAndRender();
        });
      });
  
      updateTotals(subtotal);
    }
  
    function updateTotals(subtotal) {
      if (subtotalSpan) subtotalSpan.textContent = `₱ ${subtotal.toFixed(2)}`;
      if (deliverySpan) deliverySpan.textContent = `₱ ${DELIVERY_FEE.toFixed(2)}`;
      const total = subtotal + DELIVERY_FEE;
      if (totalPayDiv) totalPayDiv.textContent = `Total to pay ₱ ${total.toFixed(2)}`;
      localStorage.setItem('orderTotal', total.toFixed(2));
      localStorage.setItem('orderSubtotal', subtotal.toFixed(2));
      localStorage.setItem('deliveryFee', DELIVERY_FEE.toFixed(2));
    }
  
    function saveAndRender() {
      localStorage.setItem('cart', JSON.stringify(cart));
      renderBasket();
    }
  
    if (checkoutFinalButton) {
      checkoutFinalButton.addEventListener('click', () => {
        if (cart.length === 0) {
          alert('Your cart is empty!');
          return;
        }
  
        localStorage.setItem('checkoutTime', new Date().toISOString());
        window.location.href = 'payment.html';
      });
    }
  
    renderBasket();
  });
  