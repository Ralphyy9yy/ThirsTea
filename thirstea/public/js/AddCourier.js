document.addEventListener('DOMContentLoaded', () => {
    const addCourierForm = document.getElementById('addCourierForm');
    const courierTableBody = document.getElementById('courierTableBody');

    if (!addCourierForm || !courierTableBody) {
        console.error('Required elements not found.');
        return;
    }

    // Load couriers on page load
    loadCouriers();

    // Handle form submission
    addCourierForm.addEventListener('submit', function (e) {
        e.preventDefault();

        const formData = new FormData(addCourierForm);

        fetch('http://localhost:3000/thirstea/backend/add_courier.php', {
            method: 'POST',
            body: formData
        })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Courier added successfully!');
                    addCourierForm.reset();
                    window.location.href = '../html/User.html'; 
                    loadCouriers(); // Refresh courier list
                } else {
                    alert('Error: ' + (data.message || 'Failed to add courier.'));
                }
            })
            .catch(error => {
                console.error('Error adding courier:', error);
                alert('An error occurred while adding the courier. Please try again.');
            });
    });

    // Function to load and display couriers
    function loadCouriers() {
        fetch('http://localhost:3000/thirstea/backend/get_courier.php')
            .then(response => response.json())
            .then(data => {
                if (data.success && Array.isArray(data.couriers)) {
                    courierTableBody.innerHTML = '';

                    if (data.couriers.length === 0) {
                        courierTableBody.innerHTML = `<tr><td colspan="7" style="text-align:center;">No couriers found.</td></tr>`;
                        return;
                    }

                    data.couriers.forEach(courier => {
                        const tr = document.createElement('tr');
                        tr.innerHTML = `
                            <td>${escapeHtml(courier.firstName)}</td>
                            <td>${escapeHtml(courier.lastName)}</td>
                            <td>${escapeHtml(courier.contactNumber)}</td>
                            <td>${escapeHtml(courier.vehicleType)}</td>
                            <td>${escapeHtml(courier.vehicleLicensePlate)}</td>
                            <td>${escapeHtml(courier.username)}</td>
                        `;
                        courierTableBody.appendChild(tr);
                    });
                } else {
                    courierTableBody.innerHTML = `<tr><td colspan="7" style="color:red; text-align:center;">Failed to load couriers.</td></tr>`;
                }
            })
            .catch(error => {
                console.error('Error loading couriers:', error);
                courierTableBody.innerHTML = `<tr><td colspan="7" style="color:red; text-align:center;">Error loading couriers.</td></tr>`;
            });
    }

    // Simple function to escape HTML to prevent XSS
    function escapeHtml(text) {
        if (!text) return '';
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
});
