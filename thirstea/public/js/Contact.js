document.getElementById('contactForm').addEventListener('submit', function (e) {
    e.preventDefault();

    const username = document.getElementById('username').value.trim();
    const email = document.getElementById('email').value.trim();
    const message = document.getElementById('message').value.trim();

    const formData = new FormData();
    formData.append('username', username);
    formData.append('email', email);
    formData.append('message', message);

    fetch('http://localhost:3000/thirstea/backend/contact.php', { // adjust if your PHP file is in a different folder
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        alert(data.message);
        if (data.success) {
            document.getElementById('contactForm').reset();
        }
    })
    .catch(error => {
        console.error("Error:", error);
        alert("Something went wrong!");
    });
});
