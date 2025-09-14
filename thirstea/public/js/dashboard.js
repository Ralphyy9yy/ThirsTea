document.addEventListener('DOMContentLoaded', () => {
    const totalOrdersEl = document.getElementById('total-orders');
    const totalRevenueEl = document.getElementById('total-revenue');
    const activeCouriersEl = document.getElementById('active-couriers');
    const pendingDeliveriesEl = document.getElementById('pending-deliveries');

    fetch('http://localhost:3000/thirstea/backend/dashboard_stats.php')
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! Status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                totalOrdersEl.textContent = data.totalOrders ?? '0';
                totalRevenueEl.textContent = `₱${(data.totalRevenue ?? 0).toFixed(2)}`;
                activeCouriersEl.textContent = data.activeCouriers ?? '0';
                pendingDeliveriesEl.textContent = data.pendingDeliveries ?? '0';
            } else {
                totalOrdersEl.textContent = 'Error';
                totalRevenueEl.textContent = 'Error';
                activeCouriersEl.textContent = 'Error';
                pendingDeliveriesEl.textContent = 'Error';
                console.error('Failed to load dashboard stats:', data.message);
            }
        })
        .catch(error => {
            totalOrdersEl.textContent = 'Error';
            totalRevenueEl.textContent = 'Error';
            activeCouriersEl.textContent = 'Error';
            pendingDeliveriesEl.textContent = 'Error';
            console.error('Error fetching dashboard stats:', error);
        });
});