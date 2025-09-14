document.addEventListener('DOMContentLoaded', () => {
  const tbody = document.querySelector('table tbody');
  const logoutBtn = document.getElementById('logoutBtn');

  if (!tbody) {
    console.error('Table body element not found.');
    return;
  }

  if (!logoutBtn) {
    console.warn('Logout button not found.');
  }

  // Get courierId from localStorage or redirect if not logged in
  const courierId = parseInt(localStorage.getItem('courierId'));
  if (!courierId) {
    alert('Please login first.');
    window.location.href = '../html/1st.html'; // Change to your login page
    return;
  }

  // Status options
  const statusOptions = ['Pending', 'Picked Up', 'Delivered'];

  // Load orders assigned to courier
  async function loadOrders() {
    try {
      const res = await fetch(`http://localhost:3000/thirstea/backend/get_assigned_orders.php?courierId=${courierId}`);
      if (!res.ok) throw new Error('Failed to fetch orders');
      const data = await res.json();
      if (!data.success) throw new Error(data.message || 'Failed to load orders');

      tbody.innerHTML = '';
      if (!data.orders.length) {
        tbody.innerHTML = '<tr><td colspan="5" style="text-align:center;">No assigned deliveries.</td></tr>';
        return;
      }

      data.orders.forEach(order => {
        const tr = document.createElement('tr');

        // Create dropdown for status
        const select = document.createElement('select');
        statusOptions.forEach(status => {
          const option = document.createElement('option');
          option.value = status;
          option.textContent = status;
          if (status.toLowerCase() === order.status.toLowerCase()) {
            option.selected = true;
            select.setAttribute('data-prev', status); // Initialize data-prev attribute
          }
          select.appendChild(option);
        });

        // On status change, update backend
        select.addEventListener('change', () => updateStatus(order.orderId, select.value, select));

        tr.innerHTML = `
          <td>${order.customerName}</td>
          <td>${order.address}</td>
          <td>₱${order.amount.toFixed(2)}</td>
          <td>${order.date}</td>
        `;
        const tdAction = document.createElement('td');
        tdAction.appendChild(select);
        tr.appendChild(tdAction);

        tbody.appendChild(tr);
      });
    } catch (error) {
      console.error(error);
      tbody.innerHTML = `<tr><td colspan="5" style="color:red; text-align:center;">Error loading orders.</td></tr>`;
    }
  }

  // Update order status in backend
  async function updateStatus(orderId, newStatus, selectElement) {
    if (!confirm(`Change status to "${newStatus}"?`)) {
      // Revert selection if cancelled
      selectElement.value = selectElement.getAttribute('data-prev') || newStatus;
      return;
    }

    selectElement.disabled = true;

    try {
      const res = await fetch('http://localhost:3000/thirstea/backend/updated_order_status.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ orderId, status: newStatus })
      });
      const data = await res.json();

      if (data.success) {
        // Update previous status attribute
        selectElement.setAttribute('data-prev', newStatus);

        if (newStatus.toLowerCase() === 'delivered') {
          // Remove order row from table
          const row = selectElement.closest('tr');
          if (row) {
            row.remove();
          }

          // Show message if no orders left
          if (tbody.children.length === 0) {
            tbody.innerHTML = '<tr><td colspan="5" style="text-align:center;">No assigned deliveries.</td></tr>';
          }
        }
      } else {
        alert('Failed to update order status: ' + (data.message || 'Unknown error'));
        // Revert selection on failure
        selectElement.value = selectElement.getAttribute('data-prev') || newStatus;
      }
    } catch (error) {
      console.error(error);
      alert('Error updating order status. Please try again.');
      selectElement.value = selectElement.getAttribute('data-prev') || newStatus;
    } finally {
      selectElement.disabled = false;
    }
  }

  // Logout button
  if (logoutBtn) {
    logoutBtn.addEventListener('click', e => {
      e.preventDefault();
      localStorage.removeItem('courierId');
      window.location.href = '../html/1st.html'; // Adjust as needed
    });
  }

  // Initial load
  loadOrders();
});
