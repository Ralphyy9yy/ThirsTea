document.getElementById('loginForm').addEventListener('submit', function(event) {
    event.preventDefault();
    const username = document.getElementById('username').value;
    const password = document.getElementById('password').value;

    // You can add your authentication logic here
    if (username === 'user' && password === 'password') {
        alert('Login successful!');
    } else {
        alert('Login failed. Please check your credentials.');
    }
});
