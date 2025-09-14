document.addEventListener('DOMContentLoaded', () => {
    const addCourierForm = document.getElementById('addCourierForm');
    const courierTableBody = document.getElementById('courierTableBody');
  
    // Function to fetch and display couriers
    function loadCouriers() {
      fetch('http://localhost:3000/thirstea/backend/get_courier.php')
        .then(response => response.json())
        .then(data => {
          courierTableBody.innerHTML = ''; // Clear existing table data
          if (data.success && data.couriers.length > 0) {
            data.couriers.forEach(courier => {
              const row = document.createElement('tr');
              row.innerHTML = `
                <td>${courier.CourierID}</td>
                <td>${courier.FirstName}</td>
                <td>${courier.LastName}</td>
                <td>${courier.ContactNumber}</td>
                <td>${courier.VehicleType}</td>
                <td>${courier.VehicleLicensePlate}</td>
              `;
              courierTableBody.appendChild(row);
            });
          } else {
            courierTableBody.innerHTML = '<tr><td colspan="6">No couriers found.</td></tr>';
          }
        })
        .catch(error => {
          console.error('Error loading couriers:', error);
          courierTableBody.innerHTML = '<tr><td colspan="6">Error loading couriers.</td></tr>';
        });
    }
  
    if (courierTableBody) {
      loadCouriers();
    }
  
    // Handle add courier form submission (form-encoded)
    if (addCourierForm) {
      addCourierForm.addEventListener('submit', function(e) {
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
            if (courierTableBody) {
              loadCouriers();
            }
          } else {
            alert('Failed to add courier: ' + (data.message || 'Unknown error'));
          }
        })
        .catch(error => {
          console.error('Error adding courier:', error);
          alert('Error adding courier. Please try again.');
        });
      });
    }
  
    // Handle login form submission (JSON)
    const loginForm = document.getElementById('loginForm');
  
    if (loginForm) {
      loginForm.addEventListener('submit', function(e) {
        e.preventDefault();
  
        const username = loginForm.username.value.trim();
        const password = loginForm.password.value;
  
        fetch('http://localhost:3000/thirstea/backend/courier_login.php', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({ username, password })
        })
        .then(response => response.json())
        .then(data => {
          if (data.success) {
            // Store courierId in localStorage for session persistence
            localStorage.setItem('courierId', data.courierId);
  
            // Redirect to dashboard
            window.location.href = '../html/courier-dashboard.html'; // Adjust path if needed
          } else {
            alert(data.message || 'Invalid username or password.');
          }
        })
        .catch(error => {
          console.error('Login error:', error);
          alert('An error occurred. Please try again.');
        });
      });
    }
  });
  