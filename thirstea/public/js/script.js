document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('registrationForm');

    const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    const phonePattern = /^[0-9+\-\s]{7,15}$/;

    form.addEventListener('submit', function(e) {
        e.preventDefault();

        const requiredFields = [
            { id: 'firstName', name: 'First Name' },
            { id: 'lastName', name: 'Surname' },
            { id: 'address', name: 'Address' },
            { id: 'username', name: 'Username' },
            { id: 'email', name: 'Email' },
            { id: 'phoneNumber', name: 'Phone Number' },
            { id: 'password', name: 'Password' }
        ];

        const emptyField = requiredFields.find(field => {
            const input = document.getElementById(field.id);
            return !input.value.trim();
        });

        if (emptyField) {
            alert(`Please enter your ${emptyField.name}.`);
            document.getElementById(emptyField.id).focus();
            return;
        }

        const emailInput = document.getElementById('email').value.trim();
        if (!emailPattern.test(emailInput)) {
            alert('Please enter a valid email address.');
            document.getElementById('email').focus();
            return;
        }

        const phoneInput = document.getElementById('phoneNumber').value.trim();
        if (!phonePattern.test(phoneInput)) {
            alert('Please enter a valid phone number.');
            document.getElementById('phoneNumber').focus();
            return;
        }

        // Disable submit button to prevent multiple submits
        const submitBtn = form.querySelector('button[type="submit"]');
        if (submitBtn) submitBtn.disabled = true;

        form.submit();
    });
});
