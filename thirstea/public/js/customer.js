document.addEventListener('DOMContentLoaded', () => {
    const tbody = document.querySelector('#customersTable tbody');
    const table = document.getElementById('customersTable');
  
    // Create and insert a search input above the table
    const searchContainer = document.createElement('div');
    searchContainer.style.margin = '1rem 0';
    const searchInput = document.createElement('input');
    searchInput.type = 'search';
    searchInput.placeholder = 'Search customers...';
    searchInput.style.padding = '0.5rem';
    searchInput.style.width = '100%';
    searchInput.setAttribute('aria-label', 'Search customers');
    searchContainer.appendChild(searchInput);
    table.parentNode.insertBefore(searchContainer, table);
  
    // Create and insert pagination controls below the table
    const paginationContainer = document.createElement('div');
    paginationContainer.style.margin = '1rem 0';
    paginationContainer.style.display = 'flex';
    paginationContainer.style.justifyContent = 'center';
    paginationContainer.style.alignItems = 'center';
    paginationContainer.style.gap = '0.5rem';
    table.parentNode.insertBefore(paginationContainer, table.nextSibling);
  
    const btnFirst = createButton('First');
    const btnPrev = createButton('Prev');
    const pageInfo = document.createElement('span');
    const btnNext = createButton('Next');
    const btnLast = createButton('Last');
  
    paginationContainer.append(btnFirst, btnPrev, pageInfo, btnNext, btnLast);
  
    // Pagination variables
    const pageSize = 10;
    let currentPage = 1;
  
    let customers = [];
    let filteredCustomers = [];
    let currentSort = { column: null, ascending: true };
  
    function createButton(text) {
      const btn = document.createElement('button');
      btn.textContent = text;
      btn.type = 'button';
      btn.style.padding = '0.3rem 0.6rem';
      btn.style.cursor = 'pointer';
      btn.setAttribute('aria-label', text + ' page');
      return btn;
    }
  
    // Load customers from backend
    async function loadCustomers() {
      try {
        showLoading();
        const response = await fetch('http://localhost:3000/thirstea/backend/get_customer.php');
        if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);
  
        const data = await response.json();
  
        if (!data.success || !data.customers.length) {
          showMessage(data.message || 'No customers found');
          return;
        }
  
        customers = data.customers;
        filteredCustomers = [...customers];
        currentPage = 1;
        renderTablePage();
        updatePagination();
      } catch (error) {
        console.error('Error:', error);
        showMessage('Error loading customers. Please try again later.');
      }
    }
  
    // Show loading message
    function showLoading() {
      tbody.innerHTML = `<tr><td colspan="3" style="text-align:center; font-style: italic;">Loading customers...</td></tr>`;
    }
  
    // Show message in table
    function showMessage(message) {
      tbody.innerHTML = `<tr><td colspan="3" style="text-align:center; color: #a00;">${message}</td></tr>`;
    }
  
    // Render current page of customers
    function renderTablePage() {
      const startIdx = (currentPage - 1) * pageSize;
      const pageData = filteredCustomers.slice(startIdx, startIdx + pageSize);
  
      if (!pageData.length) {
        showMessage('No matching customers found.');
        return;
      }
  
      tbody.innerHTML = '';
      pageData.forEach(customer => {
        const tr = document.createElement('tr');
        tr.innerHTML = `
          <td>${safeHtml(customer.name)}</td>
          <td>${safeHtml(customer.phone)}</td>
          <td>${safeHtml(customer.email)}</td>
        `;
        tbody.appendChild(tr);
      });
    }
  
    // Escape HTML to prevent XSS
    function safeHtml(str) {
      const div = document.createElement('div');
      div.textContent = str || '';
      return div.innerHTML;
    }
  
    // Filter customers based on search input
    searchInput.addEventListener('input', () => {
      const query = searchInput.value.trim().toLowerCase();
      filteredCustomers = customers.filter(cust =>
        (cust.name && cust.name.toLowerCase().includes(query)) ||
        (cust.phone && cust.phone.toLowerCase().includes(query)) ||
        (cust.email && cust.email.toLowerCase().includes(query))
      );
      currentPage = 1;
      renderTablePage();
      updatePagination();
    });
  
    // Sort functionality
    const headers = table.querySelectorAll('thead th');
    headers.forEach((th, index) => {
      th.style.cursor = 'pointer';
      th.setAttribute('tabindex', '0');
      th.setAttribute('role', 'button');
      th.setAttribute('aria-label', `Sort by ${th.textContent.trim()}`);
  
      th.addEventListener('click', () => sortByColumn(index));
      th.addEventListener('keydown', e => {
        if (e.key === 'Enter' || e.key === ' ') {
          e.preventDefault();
          sortByColumn(index);
        }
      });
    });
  
    function sortByColumn(colIndex) {
      const columns = ['name', 'phone', 'email'];
      const column = columns[colIndex];
      if (!column) return;
  
      if (currentSort.column === column) {
        currentSort.ascending = !currentSort.ascending; // toggle direction
      } else {
        currentSort.column = column;
        currentSort.ascending = true;
      }
  
      filteredCustomers.sort((a, b) => {
        const valA = (a[column] || '').toLowerCase();
        const valB = (b[column] || '').toLowerCase();
  
        if (valA < valB) return currentSort.ascending ? -1 : 1;
        if (valA > valB) return currentSort.ascending ? 1 : -1;
        return 0;
      });
  
      currentPage = 1;
      renderTablePage();
      updatePagination();
      updateSortIndicators(colIndex);
    }
  
    function updateSortIndicators(activeIndex) {
      headers.forEach((th, i) => {
        th.removeAttribute('aria-sort');
        th.classList.remove('asc', 'desc');
        if (i === activeIndex) {
          th.setAttribute('aria-sort', currentSort.ascending ? 'ascending' : 'descending');
          th.classList.add(currentSort.ascending ? 'asc' : 'desc');
        }
      });
    }
  
    // Pagination controls events
    btnFirst.addEventListener('click', () => {
      if (currentPage !== 1) {
        currentPage = 1;
        renderTablePage();
        updatePagination();
      }
    });
  
    btnPrev.addEventListener('click', () => {
      if (currentPage > 1) {
        currentPage--;
        renderTablePage();
        updatePagination();
      }
    });
  
    btnNext.addEventListener('click', () => {
      if (currentPage < totalPages()) {
        currentPage++;
        renderTablePage();
        updatePagination();
      }
    });
  
    btnLast.addEventListener('click', () => {
      if (currentPage !== totalPages()) {
        currentPage = totalPages();
        renderTablePage();
        updatePagination();
      }
    });
  
    // Calculate total pages
    function totalPages() {
      return Math.ceil(filteredCustomers.length / pageSize) || 1;
    }
  
    // Update pagination buttons and info
    function updatePagination() {
      pageInfo.textContent = `Page ${currentPage} of ${totalPages()}`;
  
      btnFirst.disabled = currentPage === 1;
      btnPrev.disabled = currentPage === 1;
      btnNext.disabled = currentPage === totalPages();
      btnLast.disabled = currentPage === totalPages();
    }
  
    // Initial load
    loadCustomers();
  });
  