document.addEventListener('DOMContentLoaded', () => {
    const orderTotalElement = document.getElementById('order-total');
    const total = localStorage.getItem('orderTotal');
  
    if (total && orderTotalElement) {
      orderTotalElement.textContent = `Total Paid: ₱${parseFloat(total).toFixed(2)}`;
      // Optionally clear the stored total after displaying
      localStorage.removeItem('orderTotal');
    }
  });
  