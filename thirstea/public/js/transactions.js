document.addEventListener('DOMContentLoaded', () => {
    const tbody = document.getElementById('transactions-tbody');
    const loadingMessage = document.getElementById('loading-message');
  
    async function fetchTransactions() {
      loadingMessage.style.display = 'block';
      tbody.innerHTML = '';
  
      try {
        const res = await fetch('http://localhost:3000/thirstea/backend/get_transactions.php', {
          method: 'GET',
          headers: {
            'Accept': 'application/json'
          },
          credentials: 'include' // if your backend uses cookies/sessions
        });
  
        if (!res.ok) {
          throw new Error(`Failed to fetch transactions: ${res.status} ${res.statusText}`);
        }
  
        const transactions = await res.json();
  
        loadingMessage.style.display = 'none';
  
        if (!Array.isArray(transactions) || transactions.length === 0) {
          tbody.innerHTML = `<tr><td colspan="6" style="text-align:center;">No transactions to display.</td></tr>`;
          return;
        }
  
        tbody.innerHTML = transactions.map(tx => `
          <tr>
            <td>${escapeHtml(tx.Transaction_ID?.toString() || '')}</td>
            <td>${escapeHtml(tx.Order_ID?.toString() || '')}</td>
            <td>₱${isNaN(parseFloat(tx.Amount)) ? '0.00' : parseFloat(tx.Amount).toFixed(2)}</td>
            <td>${escapeHtml(tx.Payment_Method || '')}</td>
            <td>${formatDate(tx.Timestamp)}</td>
            <td>${escapeHtml(tx.Status || '')}</td>
          </tr>
        `).join('');
  
      } catch (error) {
        loadingMessage.style.display = 'none';
        tbody.innerHTML = `<tr><td colspan="6" style="text-align:center; color:red;">Error loading transactions.</td></tr>`;
        console.error('Fetch transactions error:', error);
      }
    }
  
    // Basic HTML escape function
    function escapeHtml(text) {
      if (!text) return '';
      return text.replace(/[&<>"']/g, m => ({
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#39;'
      })[m]);
    }
  
    // Format date safely, fallback to empty string if invalid
    function formatDate(dateString) {
      const date = new Date(dateString);
      return isNaN(date) ? '' : date.toLocaleString();
    }
  
    fetchTransactions();
  });
  