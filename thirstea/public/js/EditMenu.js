document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('editMenuForm');
    const currentImageContainer = document.getElementById('currentImageContainer');
    const cancelBtn = document.getElementById('cancelBtn');
    const submitBtn = form.querySelector('button[type="submit"]');

    const urlParams = new URLSearchParams(window.location.search);
    const menuItemId = urlParams.get('id');

    if (!menuItemId) {
        alert('No menu item ID provided.');
        window.location.href = '../html/Menu.html';
        return;
    }

    async function loadMenuItem() {
        try {
            const response = await fetch(`http://localhost:3000/thirstea/backend/get_menu_item.php?id=${menuItemId}`);
            const data = await response.json();

            if (!data.success) {
                alert('Failed to load menu item: ' + (data.message || 'Unknown error'));
                window.location.href = '../html/Menu.html';
                return;
            }

            const item = data.item;

            form.itemName.value = item.ItemName;
            form.category.value = item.category;
            form.itemPrice.value = item.ItemPrice;

            currentImageContainer.innerHTML = '';
            if (item.image_url) {
                const img = document.createElement('img');
                img.src = item.image_url;
                img.alt = item.ItemName;
                img.style.maxWidth = '150px';
                img.style.borderRadius = '8px';
                currentImageContainer.appendChild(img);
            } else {
                currentImageContainer.textContent = 'No image available';
            }
        } catch (error) {
            console.error('Error loading menu item:', error);
            alert('An error occurred while loading the menu item.');
            window.location.href = '../html/Menu.html';
        }
    }

    loadMenuItem();

    form.addEventListener('submit', async(e) => {
        e.preventDefault();

        const itemName = form.itemName.value.trim();
        const category = form.category.value.trim();
        const itemPrice = form.itemPrice.value.trim();

        if (!itemName || !category || !itemPrice || Number(itemPrice) <= 0) {
            alert('Please fill in all required fields with valid values.');
            return;
        }

        submitBtn.disabled = true;
        submitBtn.textContent = 'Updating...';

        const formData = new FormData(form);
        formData.append('id', menuItemId);

        try {
            const response = await fetch('http://localhost:3000/thirstea/backend/edit_menu_item.php', {
                method: 'POST',
                body: formData,
            });

            // Get raw text for debugging
            const text = await response.text();
            console.log('Raw response:', text);

            let result;
            try {
                result = JSON.parse(text);
            } catch (jsonError) {
                throw new Error('Invalid JSON response from server.');
            }

            if (result.success) {
                alert('Menu item updated successfully!');
                window.location.href = '../html/Menu.html';
            } else {
                alert('Failed to update menu item: ' + (result.message || 'Unknown error'));
            }
        } catch (error) {
            console.error('Error updating menu item:', error);
            alert('An error occurred while updating the menu item.');
        } finally {
            submitBtn.disabled = false;
            submitBtn.textContent = 'Update Menu Item';
        }
    });

    cancelBtn.addEventListener('click', () => {
        window.location.href = '../html/Menu.html';
    });
});