document.addEventListener('DOMContentLoaded', () => {
    const tbody = document.getElementById('orders-tbody');
    const apiUrl = 'http://localhost:3000/thirstea/backend/get_orders.php'; // Update if needed

    // Map statuses to CSS class names
    const statusClassMap = {
        'pending': 'status-pending',
        'processing': 'status-processing',
        'on delivery': 'status-ondelivery',
        'done': 'status-done',
        'delivered': 'status-delivered'
    };

    fetch(apiUrl, { method: 'GET', mode: 'cors', cache: 'no-cache' })
        .then(response => {
            if (!response.ok) throw new Error(`HTTP error! Status: ${response.status}`);
            return response.json();
        })
        .then(data => {
            tbody.innerHTML = '';

            if (data.success && Array.isArray(data.orders) && data.orders.length > 0) {
                data.orders.forEach(order => {
                    const tr = document.createElement('tr');

                    // Order ID
                    const tdOrderID = document.createElement('td');
                    tdOrderID.textContent = order.OrderID;
                    tr.appendChild(tdOrderID);

                    // Customer
                    const tdCustomer = document.createElement('td');
                    tdCustomer.textContent = order.Customer || 'Unknown';
                    tr.appendChild(tdCustomer);

                    // Amount
                    const tdAmount = document.createElement('td');
                    tdAmount.textContent = `₱${parseFloat(order.Amount).toFixed(2)}`;
                    tr.appendChild(tdAmount);

                    // Date
                    const tdDate = document.createElement('td');
                    tdDate.textContent = order.Date;
                    tr.appendChild(tdDate);

                    // Status with highlight
                    const tdStatus = document.createElement('td');
                    const statusText = (order.Status || 'Unknown').toLowerCase();
                    tdStatus.textContent = order.Status || 'Unknown';

                    // Add CSS class based on status
                    if (statusClassMap[statusText]) {
                        tdStatus.classList.add(statusClassMap[statusText]);
                    }

                    tr.appendChild(tdStatus);

                    tbody.appendChild(tr);
                });
            } else {
                tbody.innerHTML = `<tr><td colspan="5" style="text-align:center;">No orders found.</td></tr>`;
            }
        })
        .catch(error => {
            tbody.innerHTML = `<tr><td colspan="5" style="color:red; text-align:center;">Error loading orders: ${error.message}</td></tr>`;
            console.error('Error fetching orders:', error);
        });
});