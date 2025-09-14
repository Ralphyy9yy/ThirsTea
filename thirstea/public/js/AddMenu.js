document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('menuForm');

    form.addEventListener('submit', async(e) => {
        e.preventDefault();

        // Basic validation
        const itemName = form.itemName.value.trim();
        const category = form.category.value.trim();
        const itemPrice = form.itemPrice.value.trim();

        if (!itemName || !category || !itemPrice || Number(itemPrice) <= 0) {
            alert('Please fill in all required fields with valid values.');
            return;
        }

        // Prepare form data (includes file upload if any)
        const formData = new FormData(form);

        try {
            const response = await fetch('http://localhost:3000/thirstea/backend/add_menu_item.php', {
                method: 'POST',
                body: formData,
            });

            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }

            const result = await response.json();

            if (result.success) {
                alert('Menu item added successfully!');
                // Redirect to menu management page to see the updated list
                window.location.href = '../html/Menu.html'; // Adjust path if needed
            } else {
                alert('Failed to add menu item: ' + (result.message || 'Unknown error'));
            }
        } catch (error) {
            console.error('Error:', error);
            alert('An error occurred while submitting the form.');
        }
    });
});