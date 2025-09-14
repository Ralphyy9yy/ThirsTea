document.addEventListener("DOMContentLoaded", () => {
  // Hamburger menu toggle
  const hamburger = document.getElementById("hamburger");
  const navMenu = document.getElementById("navMenu");
  if (hamburger && navMenu) {
    hamburger.addEventListener("click", () => {
      navMenu.classList.toggle("active");
      hamburger.classList.toggle("active");
    });
  }

  // Profile dropdown toggle (Login/Logout preserved)
  const userProfile = document.querySelector(".user-profile");
  const profileDropdown = userProfile?.querySelector(".profile-dropdown");
  const loginLink = profileDropdown?.querySelector("#login-link");
  const logoutBtn = profileDropdown?.querySelector("#logout-btn");

  // Function to update Login/Logout visibility based on user login status
  function updateLoginLogoutUI() {
    const user = JSON.parse(localStorage.getItem("user"));
    if (user && (user.username || user.userName)) {
      // User is logged in
      if (loginLink) loginLink.style.display = "none";
      if (logoutBtn) logoutBtn.style.display = "block";
    } else {
      // User not logged in
      if (loginLink) loginLink.style.display = "block";
      if (logoutBtn) logoutBtn.style.display = "none";
    }
  }

  updateLoginLogoutUI();

  // Logout button click handler
  if (logoutBtn) {
    logoutBtn.addEventListener("click", () => {
      localStorage.removeItem("user");
      updateLoginLogoutUI();
      alert("You have been logged out.");
      // Optionally redirect to login page
      window.location.href = "../html/1st.html";
    });
  }

  // Profile dropdown toggle on click
  if (userProfile && profileDropdown) {
    userProfile.addEventListener("click", (e) => {
      if (
        e.target.classList.contains("profile-logo") ||
        e.target.classList.contains("user-profile")
      ) {
        const isOpen = profileDropdown.style.display === "block";
        profileDropdown.style.display = isOpen ? "none" : "block";
        userProfile.setAttribute("aria-expanded", !isOpen);
        profileDropdown.setAttribute("aria-hidden", isOpen);
      }
    });

    // Close dropdown on outside click
    document.addEventListener("click", (e) => {
      if (!userProfile.contains(e.target)) {
        profileDropdown.style.display = "none";
        userProfile.setAttribute("aria-expanded", "false");
        profileDropdown.setAttribute("aria-hidden", "true");
      }
    });

    // Close dropdown on Escape key
    document.addEventListener("keydown", (e) => {
      if (e.key === "Escape") {
        profileDropdown.style.display = "none";
        userProfile.setAttribute("aria-expanded", "false");
        profileDropdown.setAttribute("aria-hidden", "true");
      }
    });
  }

  // Helper to add sizes to Fries and Drinks
  function addSizesToProducts(products) {
    return products.map(product => {
      if (
        product.category === "Snacks" &&
        product.ItemName &&
        product.ItemName.toLowerCase().includes("fries")
      ) {
        return {
          ...product,
          sizes: [
            { label: "Regular", price: product.ItemPrice },
            { label: "Large", price: product.ItemPrice + 20 }
          ]
        };
      }
      if (product.category === "Drinks") {
        return {
          ...product,
          sizes: [
            { label: "Small", price: product.ItemPrice },
            { label: "Medium", price: product.ItemPrice + 20 },
            { label: "Large", price: product.ItemPrice + 40 }
          ]
        };
      }
      return product;
    });
  }

  // Fetch and display products
  async function fetchProducts() {
    try {
      const response = await fetch(
        "http://localhost:3000/thirstea/backend/products.php"
      );
      if (!response.ok)
        throw new Error(`HTTP error! status: ${response.status}`);

      let products = await response.json();
      console.log('products:',products);
      const snacks = products.filter((p) => p.category === "Snacks");
      console.log('snacks:',snacks);
      const drinks = products.filter((p) => p.category === "Drinks");
      console.log('drinks:',drinks);

      displayProducts(snacks, "snacks-container");
      displayProducts(drinks, "drinks-container");
    } catch (error) {
      console.error("Failed to fetch products:", error);
      const snacksContainer = document.getElementById("snacks-container");
      const drinksContainer = document.getElementById("drinks-container");
      if (snacksContainer)
        snacksContainer.innerHTML = "<p>Failed to load products.</p>";
      if (drinksContainer)
        drinksContainer.innerHTML = "<p>Failed to load products.</p>";
    }
  }

  // Display products in container
  function displayProducts(products, containerId) {
    const container = document.getElementById(containerId);
    if (!container) return;

    container.innerHTML = "";
    if (!products.length) {
      container.innerHTML = "<p>No products found.</p>";
      return;
    }

    products.forEach((product) => {
      const itemDiv = createProductElement(product);
      container.appendChild(itemDiv);
    });
  }

  // Create product element with size selector if sizes exist
  function createProductElement(product) {
    const itemDiv = document.createElement("div");
    itemDiv.className = "item";

    const isAvailable = product.status === "available";


    let sizeSelectorHtml = "";
    let sizeOptions = [];
    let defaultPrice = product.ItemPrice;

    if (Array.isArray(product.sizes) && product.sizes.length) {
      sizeOptions = product.sizes;
      sizeSelectorHtml = `
        <div class="size-controls">
          <label for="size-${product.MenuItemID}">Size:</label>
          <select class="size-select" id="size-${product.MenuItemID}">
            ${sizeOptions
              .map(
                (size, i) =>
                  `<option value="${i}" data-price="${size.price}">${size.label} (₱${Number(size.price).toFixed(2)})</option>`
              )
              .join("")}
          </select>
        </div>
      `;
      defaultPrice = Number(sizeOptions[0].price);
    }
    
    

    itemDiv.innerHTML = `
    <img src="${product.image_url}" alt="${product.ItemName}">
    <p>${product.ItemName}</p>
    ${sizeSelectorHtml}
    <p class="item-price">₱<span class="price-value">${defaultPrice}</span></p>
    
    ${
      isAvailable
        ? `
      <div class="quantity-controls">
        <button class="quantity-btn quantity-decrease" data-product-id="${product.MenuItemID}">-</button>
        <input type="number" class="quantity-input" value="1" min="1" data-product-id="${product.MenuItemID}">
        <button class="quantity-btn quantity-increase" data-product-id="${product.MenuItemID}">+</button>
      </div>
      <div class="button-container">
        <button class="button add-to-order" data-product-id="${product.MenuItemID}">
          Add to Order
        </button>
        <button class="button order-now" data-product-id="${product.MenuItemID}">
          Order Now
        </button>
      </div>
      `
        : `<p style="color: red; font-weight: bold;">Not Available</p>`
    }
  `;
  
    const decreaseButton = itemDiv.querySelector(".quantity-decrease");
    const increaseButton = itemDiv.querySelector(".quantity-increase");
    const addButton = itemDiv.querySelector(".add-to-order");
    const orderNowButton = itemDiv.querySelector(".order-now");
    const quantityInput = itemDiv.querySelector(".quantity-input");
    const sizeSelect = itemDiv.querySelector(".size-select");
    const priceValue = itemDiv.querySelector(".price-value");

    if (sizeSelect && priceValue && sizeOptions.length) {
      sizeSelect.addEventListener("change", () => {
        const selected = sizeOptions[sizeSelect.value];
        priceValue.textContent = Number(selected.price).toFixed(2);
      });
    }
    
    

    if (decreaseButton && quantityInput) {
      decreaseButton.addEventListener("click", () => {
        updateQuantity(quantityInput, -1);
      });
    }

    if (increaseButton && quantityInput) {
      increaseButton.addEventListener("click", () => {
        updateQuantity(quantityInput, 1);
      });
    }

    if (addButton && quantityInput) {
      addButton.addEventListener("click", () => {
        let selectedSize = null;
        let price = product.ItemPrice;
        let sizeLabel = '';
        let sizePrice = product.ItemPrice;
        if (sizeSelect && sizeOptions.length) {
          selectedSize = sizeOptions[sizeSelect.value].label;
          price = sizeOptions[sizeSelect.value].price;
          sizeLabel = selectedSize;
          sizePrice = price;
        }
        addToOrder(
          product.MenuItemID,
          quantityInput.value,
          product.ItemName + (selectedSize ? ` (${selectedSize})` : ""),
          price,
          sizeLabel,
          sizePrice
        );
      });
    }

    if (orderNowButton && quantityInput) {
      orderNowButton.addEventListener("click", async (e) => {
        e.preventDefault();

        const user = JSON.parse(localStorage.getItem("user"));
        if (!user || !(user.username || user.userName)) {
          alert("Please log in to place an order.");
          window.location.href = "../html/3rd.html";
          return;
        }

        const quantity = parseInt(quantityInput.value, 10);
        if (isNaN(quantity) || quantity <= 0) {
          alert("Please select a valid quantity.");
          return;
        }

        let selectedSize = null;
        let price = product.ItemPrice;
        let sizeLabel = '';
        let sizePrice = product.ItemPrice;
        if (sizeSelect && sizeOptions.length) {
          selectedSize = sizeOptions[sizeSelect.value].label;
          price = sizeOptions[sizeSelect.value].price;
          sizeLabel = selectedSize;
          sizePrice = price;
        }

        const orderData = {
          items: [
            {
              productId: product.MenuItemID,
              quantity: quantity,
              name: product.ItemName + (selectedSize ? ` (${selectedSize})` : ""),
              price: price,
              sizeLabel: sizeLabel,
              sizePrice: sizePrice
            },
          ],
        };

        try {
          const response = await fetch(
            "http://localhost:3000/thirstea/backend/save_order.php",
            {
              method: "POST",
              headers: { "Content-Type": "application/json" },
              body: JSON.stringify(orderData),
            }
          );

          const result = await response.json();

          if (result.success) {
            window.location.href = "../html/payment.html";
          } else {
            alert("Failed to save order: " + result.message);
          }
        } catch (error) {
          console.error("Error saving order:", error);
          alert("Error saving order. Please try again.");
        }
      });
    }

    return itemDiv;
  }

  function updateQuantity(inputElement, change) {
    let currentValue = parseInt(inputElement.value) || 1;
    let newValue = currentValue + change;
    if (newValue < 1) newValue = 1;
    inputElement.value = newValue;
  }

  function addToOrder(productId, quantity, name, price, sizeLabel, sizePrice) {
    const user = JSON.parse(localStorage.getItem("user"));
    if (!user || !(user.username || user.userName)) {
      alert("Please log in to add products to your cart.");
      window.location.href = "../html/3rd.html";
      return;
    }

    const qty = parseInt(quantity, 10);
    if (isNaN(qty) || qty <= 0) {
      alert("Please select a quantity greater than zero.");
      return;
    }

    let cart = JSON.parse(localStorage.getItem("cart")) || [];
    const existingIndex = cart.findIndex(
      (item) =>
        item.productId == productId &&
        item.name === name &&
        item.sizeLabel === sizeLabel
    );

    if (existingIndex >= 0) {
      cart[existingIndex].quantity += qty;
    } else {
      cart.push({
        productId,
        quantity: qty,
        name,
        price,
        sizeLabel,
        sizePrice
      });
    }

    localStorage.setItem("cart", JSON.stringify(cart));
    alert("Product added to cart!");
  }

  // Initial fetch
  fetchProducts();
});