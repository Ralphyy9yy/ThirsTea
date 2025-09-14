document.getElementById('loginForm').addEventListener('submit', function(e) {
    e.preventDefault();

    const username = document.getElementById('username').value.trim();
    const password = document.getElementById('password').value.trim();

    const formData = new URLSearchParams();
    formData.append('username', username);
    formData.append('password', password);

    fetch('http://localhost:3000/thirstea/backend/login.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded'
            },
            body: formData,
            credentials: 'include' // Ensure session cookies are included
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                // Store full user info in localStorage
                const userData = {
                    userId: data.userId,
                    username: data.username,
                    firstName: data.firstName || '',
                    lastName: data.lastName || '',
                    role: data.role
                };
                localStorage.setItem('user', JSON.stringify(userData));

                alert(data.role === 'admin' ? "Admin login successful!" : "Login successful!");

                // Redirect based on role
                if (data.role === 'admin') {
                    window.location.href = '../html/Dashboard.html';
                } else {
                    window.location.href = '../html/6th.html'; // Customer homepage
                }
            } else {
                alert(data.message || "Login failed.");
            }
        })
        .catch(err => {
            console.error('Login Error:', err);
            alert("Something went wrong!");
        });
});
