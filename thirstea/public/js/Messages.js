document.addEventListener('DOMContentLoaded', () => {
    const ordersTbody = document.getElementById('orders-tbody');

    if (!ordersTbody) {
        console.error('Element with id "orders-tbody" not found.');
        return;
    }

    fetch('http://localhost:3000/thirstea/backend/get-messages.php') // Adjust URL to your backend endpoint
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! Status: ${response.status} ${response.statusText}`);
            }
            return response.json();
        })
        .then(data => {
            if (data.success && Array.isArray(data.messages)) {
                ordersTbody.innerHTML = ''; // Clear existing rows

                if (data.messages.length === 0) {
                    ordersTbody.innerHTML = `<tr><td colspan="4" style="text-align:center;">No orders found.</td></tr>`;
                    return;
                }

                data.messages.forEach(msg => {
                    const tr = document.createElement('tr');

                    tr.innerHTML = `
              <td>${escapeHtml(msg.username)}</td>
              <td>${escapeHtml(msg.email)}</td>
              <td>${escapeHtml(msg.Message)}</td>
              <td>${new Date(msg.submitted_at).toLocaleString()}</td>
            `;

                    ordersTbody.appendChild(tr);
                });
            } else {
                ordersTbody.innerHTML = `<tr><td colspan="4" style="color:red; text-align:center;">Failed to load orders: ${escapeHtml(data.message || 'Unknown error')}</td></tr>`;
            }
        })
        .catch(error => {
            ordersTbody.innerHTML = `<tr><td colspan="4" style="color:red; text-align:center;">Error loading orders: ${escapeHtml(error.message)}</td></tr>`;
            console.error('Error fetching orders:', error);
        });
});

// Simple function to escape HTML to prevent XSS
function escapeHtml(text) {
    if (!text) return '';
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}