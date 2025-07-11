document.addEventListener('DOMContentLoaded', () => {
  function ajaxPaginate(e) {
    e.preventDefault();
    const url = this.href;

    fetch(url, {
      headers: {
        'X-Requested-With': 'XMLHttpRequest'
      }
    })
    .then(response => response.text())
    .then(html => {
      const parser = new DOMParser();
      const doc = parser.parseFromString(html, 'text/html');
      const newContent = doc.querySelector('#tasks');
      const oldContent = document.querySelector('#tasks');
      if (newContent && oldContent) {
        oldContent.innerHTML = newContent.innerHTML;
        history.pushState(null, '', url);
        attachPaginationListeners(); // Reattach events
      }
    })
    .catch(error => console.error('AJAX pagination error:', error));
  }

  function attachPaginationListeners() {
    document.querySelectorAll('.pagination a').forEach(link => {
      link.removeEventListener('click', ajaxPaginate);
      link.addEventListener('click', ajaxPaginate);
    });
  }

  attachPaginationListeners();

  // Optional: Handle browser back/forward
  window.addEventListener('popstate', () => {
    fetch(location.href, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
      .then(response => response.text())
      .then(html => {
        const parser = new DOMParser();
        const doc = parser.parseFromString(html, 'text/html');
        const newContent = doc.querySelector('#tasks');
        const oldContent = document.querySelector('#tasks');
        if (newContent && oldContent) {
          oldContent.innerHTML = newContent.innerHTML;
          attachPaginationListeners();
        }
      });
  });
});
