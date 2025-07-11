setTimeout(function() {
    let alertBox = document.getElementById('alert-box');
    if (alertBox) {
        alertBox.classList.remove('show');
    }
}, 4000);

document.addEventListener('DOMContentLoaded', function () {
  const hash = window.location.hash;

  // Activate the tab if hash exists
  if (hash) {
    const tabTrigger = document.querySelector(`a.nav-link[href="${hash}"]`);
    if (tabTrigger) {
      new bootstrap.Tab(tabTrigger).show();
    }
  }

  // Update URL hash when tabs are clicked
  const tabLinks = document.querySelectorAll('a[data-bs-toggle="tab"]');
  tabLinks.forEach(tab => {
    tab.addEventListener('shown.bs.tab', function (e) {
      history.replaceState(null, null, e.target.getAttribute('href'));
    });
  });
});

// Ajax fetch to update the relevant section
document.addEventListener('DOMContentLoaded', () => {
  // Function to load paginated content without full reload
  function ajaxPaginate(e) {
    e.preventDefault();
    const url = this.href;
    
    fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
      .then(res => res.text())
      .then(html => {
        // Create a dummy DOM parser
        const parser = new DOMParser();
        const doc = parser.parseFromString(html, 'text/html');

        // Detect which tab is active (categories or tasks)
        const activeTab = document.querySelector('.tab-pane.active');
        if (!activeTab) return;

        if (activeTab.id === 'task') {
          // Replace categories table content
          const newContent = doc.querySelector('#task .container');
          if (newContent) {
            activeTab.querySelector('.container').innerHTML = newContent.innerHTML;
          }
        } else if (activeTab.id === 'tasks') {
          // Replace tasks table content
          const newContent = doc.querySelector('#tasks .container');
          if (newContent) {
            activeTab.querySelector('.container').innerHTML = newContent.innerHTML;
          }
        }

        // Update URL in browser address bar
        history.pushState(null, '', url);

        // Re-attach pagination event listeners
        attachPaginationListeners();
      })
      .catch(err => console.error('Pagination load failed:', err));
  }

  // Attach AJAX pagination to all current and future pagination links
  function attachPaginationListeners() {
    document.querySelectorAll('.pagination a').forEach(link => {
      link.removeEventListener('click', ajaxPaginate);
      link.addEventListener('click', ajaxPaginate);
    });
  }

  attachPaginationListeners();

  // Handle browser back/forward buttons for AJAX navigation
  window.addEventListener('popstate', () => {
    fetch(location.href, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
      .then(res => res.text())
      .then(html => {
        const parser = new DOMParser();
        const doc = parser.parseFromString(html, 'text/html');
        const activeTab = document.querySelector('.tab-pane.active');

        if (!activeTab) return;

        if (activeTab.id === 'task') {
          const newContent = doc.querySelector('#task .container');
          if (newContent) activeTab.querySelector('.container').innerHTML = newContent.innerHTML;
        } else if (activeTab.id === 'tasks') {
          const newContent = doc.querySelector('#tasks .container');
          if (newContent) activeTab.querySelector('.container').innerHTML = newContent.innerHTML;
        }
        attachPaginationListeners();
      });
  });
});
