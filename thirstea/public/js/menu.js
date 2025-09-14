document.addEventListener('DOMContentLoaded', () => {
    const container = document.getElementById('menu-items-container');
    if (!container) return;

    // Make loadMenuItems available globally for reloads
    window.loadMenuItems = loadMenuItems;

    // Load menu items from backend
    async function loadMenuItems() {
        try {
            const res = await fetch('http://localhost:3000/thirstea/backend/menu.php');
            const data = await res.json();

            if (!data.success) {
                container.textContent = 'Failed to load menu items.';
                return;
            }

            const menu = data.menu;
            if (!Array.isArray(menu) || menu.length === 0) {
                container.textContent = 'No menu items found.';
                return;
            }

            // Clear container
            container.innerHTML = '';

            // Create menu items with Edit, Remove, and Availability buttons
            menu.forEach(item => {

                console.log('Item status:', item.status);
                console.log('items:', item);

                const itemDiv = document.createElement('div');
                itemDiv.classList.add('menu-item');
                itemDiv.dataset.id = item.MenuItemID;

                // If unavailable, visually dim the item
                if (item.status === 'unavailable') {
                    itemDiv.style.opacity = '0.5';
                }

                // Image with fallback
                const img = document.createElement('img');
                img.src = item.image_url && item.image_url.trim() !== '' ? item.image_url : '../images/default.jpg';
                img.alt = item.ItemName;
                img.classList.add('menu-item-image');

                // Details container
                const details = document.createElement('div');
                details.classList.add('menu-item-details');

                const name = document.createElement('h3');
                name.textContent = item.ItemName;
                name.classList.add('menu-item-name');

                const category = document.createElement('p');
                category.textContent = `Category: ${item.category || 'N/A'}`;
                category.classList.add('menu-item-category');

                const price = document.createElement('p');
                price.textContent = `₱${parseFloat(item.ItemPrice).toFixed(2)}`;
                price.classList.add('menu-item-price');

                // Status badge

                const statusBadge = document.createElement('span');
                statusBadge.classList.add('menu-item-status');
                statusBadge.textContent = (item.status === 'unavailable') ? 'Not Available' : 'Available';
                statusBadge.style.color = (item.status === 'unavailable') ? 'red' : 'green';
                statusBadge.style.fontWeight = 'bold';
                statusBadge.style.marginLeft = '10px';

                details.appendChild(name);
                details.appendChild(category);
                details.appendChild(price);
                details.appendChild(statusBadge);

                // Actions container for buttons
                const actions = document.createElement('div');
                actions.classList.add('menu-item-actions');

                // Toggle Available/Not Available button
                const toggleAvailableBtn = document.createElement('button');
                toggleAvailableBtn.textContent = (item.status === 'unavailable') ? 'Make Available' : 'Not Available';
                toggleAvailableBtn.classList.add('toggle-available-btn');
                toggleAvailableBtn.addEventListener('click', () => toggleAvailable(item.MenuItemID, item.status));

                // Edit button
                const editBtn = document.createElement('button');
                editBtn.textContent = 'Edit';
                editBtn.classList.add('edit-btn');
                editBtn.addEventListener('click', () => onEdit(item.MenuItemID));

                // Remove button
                const removeBtn = document.createElement('button');
                removeBtn.textContent = 'Remove';
                removeBtn.classList.add('remove-btn');
                removeBtn.addEventListener('click', () => onRemove(item.MenuItemID));

                actions.appendChild(editBtn);
                actions.appendChild(removeBtn);
                actions.appendChild(toggleAvailableBtn);

                itemDiv.appendChild(img);
                itemDiv.appendChild(details);
                itemDiv.appendChild(actions);

                container.appendChild(itemDiv);
            });
        } catch (err) {
            console.error('Error loading menu:', err);
            container.textContent = 'Error loading menu items.';
        }
    }

    // Edit handler: redirect to EditMenu.html with id query param
    function onEdit(id) {
        window.location.href = `../html/EditMenu.html?id=${id}`;
    }

    // Remove handler: confirm and send delete request
    async function onRemove(id) {
        if (!confirm('Are you sure you want to remove this menu item?')) return;

        try {
            const res = await fetch('http://localhost:3000/thirstea/backend/delete_menu_item.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ id }),
            });

            const data = await res.json();

            if (data.success) {
                alert('Menu item removed successfully.');
                loadMenuItems();
            } else {
                alert('Failed to remove menu item: ' + (data.message || 'Unknown error'));
            }
        } catch (err) {
            console.error('Error removing menu item:', err);
            alert('An error occurred while removing the menu item.');
        }
    }

    // Toggle available/unavailable status
    async function toggleAvailable(id, currentStatus) {
        const newStatus = currentStatus === 'unavailable' ? 'available' : 'unavailable';
        console.log('Attempting to update status:', { id, newStatus });

        try {
            const res = await fetch('http://localhost:3000/thirstea/backend/update_menu_item.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ id, status: newStatus }),
            });
            console.log('Response status:', res.status);

            const data = await res.json();
            console.log('Response data:', data);

            if (data.success) {
                alert(`Status updated to "${newStatus}"`);
                loadMenuItems();
            } else {
                alert('Update failed: ' + (data.message || 'Unknown error'));
            }
        } catch (err) {
            console.error('Full error:', err);
            alert('Error: ' + err.message);
        }
    }

    // Initial load
    loadMenuItems();
}); // <-- Correct closing (no extra text)