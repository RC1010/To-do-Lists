// Time/date
document.addEventListener('DOMContentLoaded', () => {
    // Real-Time Date
    function updateDate() {
        let now = new Date();

        // Get the date (e.g., "03/14/2025")
        let date = now.toLocaleDateString('en-US', { 
            timeZone: 'Asia/Singapore', 
            month: '2-digit', 
            day: '2-digit', 
        });

        // Set the content
        document.getElementById('realTimeDate').textContent = date; // Display Date
    }

    // Call the function initially and then update every second
    updateDate();
    setInterval(updateDate, 1000);
});

// custom script for doughnut
Chart.register({
    id: 'centerTextPlugin',
    beforeDraw(chart) {
        const {width} = chart;
        const {height} = chart;
        const ctx = chart.ctx;
        const datasets = chart.data.datasets[0].data;

        // Only apply to doughnut
        if (chart.config.type === 'doughnut') {
            const total = datasets.reduce((a, b) => a + b, 0);
            const value = datasets[0];
            const percentage = ((value / total) * 100).toFixed(0) + '%';

            ctx.save();
            ctx.font = 'bold 18px Arial';
            ctx.textBaseline = 'middle';
            ctx.textAlign = 'center';
            ctx.fillStyle = '#333';
            ctx.fillText(percentage, width / 2, height / 2);
            ctx.restore();
        }
    }
});

// Ajax reload
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
      const newContent = doc.querySelector('#completedTasks');
      const oldContent = document.querySelector('#completedTasks');
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

  // Handle browser back/forward
  window.addEventListener('popstate', () => {
    fetch(location.href, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
      .then(response => response.text())
      .then(html => {
        const parser = new DOMParser();
        const doc = parser.parseFromString(html, 'text/html');
        const newContent = doc.querySelector('#completedTasks');
        const oldContent = document.querySelector('#completedTasks');
        if (newContent && oldContent) {
          oldContent.innerHTML = newContent.innerHTML;
          attachPaginationListeners();
        }
      });
  });
});

document.addEventListener('DOMContentLoaded', () => {

  function ajaxPaginate(e, targetSelector, containerSelector, paginationClass) {
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
      const newContent = doc.querySelector(targetSelector);
      const oldContent = document.querySelector(containerSelector);
      if (newContent && oldContent) {
        oldContent.innerHTML = newContent.innerHTML;
        history.pushState(null, '', url);
        attachPaginationListeners(); // Reattach events
      }
    })
    .catch(error => console.error(`AJAX pagination error (${paginationClass}):`, error));
  }

  function attachPaginationListeners() {
    // Completed
    document.querySelectorAll('.completed-pagination a').forEach(link => {
      link.removeEventListener('click', handleCompletedClick);
      link.addEventListener('click', handleCompletedClick);
    });

    // Pending
    document.querySelectorAll('.pending-pagination a').forEach(link => {
      link.removeEventListener('click', handlePendingClick);
      link.addEventListener('click', handlePendingClick);
    });
  }

  function handleCompletedClick(e) {
    ajaxPaginate.call(this, e, '#completedTasks', '#completedTasks', 'completed');
  }

  function handlePendingClick(e) {
    ajaxPaginate.call(this, e, '#pendingTasks', '#pendingTasks', 'pending');
  }

  attachPaginationListeners();

  // Handle browser back/forward
  window.addEventListener('popstate', () => {
    fetch(location.href, {
      headers: {
        'X-Requested-With': 'XMLHttpRequest'
      }
    })
    .then(response => response.text())
    .then(html => {
      const parser = new DOMParser();
      const doc = parser.parseFromString(html, 'text/html');

      const newCompleted = doc.querySelector('#completedTasks');
      const oldCompleted = document.querySelector('#completedTasks');
      if (newCompleted && oldCompleted) {
        oldCompleted.innerHTML = newCompleted.innerHTML;
      }

      const newPending = doc.querySelector('#pendingTasks');
      const oldPending = document.querySelector('#pendingTasks');
      if (newPending && oldPending) {
        oldPending.innerHTML = newPending.innerHTML;
      }

      attachPaginationListeners();
    });
  });
});
