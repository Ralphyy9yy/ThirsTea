document.addEventListener('DOMContentLoaded', () => {
    const tbody = document.getElementById('orders-tbody');

    fetch('http://localhost:3000/thirstea/backend/get_courier.php') // Adjust URL to your backend
        .then(response => response.json())
        .then(data => {
            tbody.innerHTML = ''; // Clear existing rows

            if (data.success && data.couriers.length > 0) {
                data.couriers.forEach(courier => {
                    const tr = document.createElement('tr');
                    tr.innerHTML = `
              <td>${courier.FirstName}</td>
              <td>${courier.LastName}</td>
              <td>${courier.ContactNumber}</td>
              <td>${courier.VehicleType}</td>
              <td>${courier.VehicleLicensePlate}</td>
            `;
                    tbody.appendChild(tr);
                });
            } else {
                tbody.innerHTML = `<tr><td colspan="5" style="text-align:center;">No couriers found.</td></tr>`;
            }
        })
        .catch(error => {
            console.error('Error fetching couriers:', error);
            tbody.innerHTML = `<tr><td colspan="5" style="color:red;">Failed to load couriers.</td></tr>`;
        });
});