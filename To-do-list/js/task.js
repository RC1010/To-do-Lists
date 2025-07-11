setTimeout(function() {
    let alertBox = document.getElementById('alert-box');
    if (alertBox) {
        alertBox.classList.remove('show');
    }
}, 4000);

document.addEventListener("DOMContentLoaded", function () {
    const hash = window.location.hash;

    if (hash) {
        const tabTriggerEl = document.querySelector(`a[href="${hash}"]`);
        if (tabTriggerEl) {
            const tab = new bootstrap.Tab(tabTriggerEl);
            tab.show();
        }
    }
});

// Delete task ajax 
    function deleteTask(taskId) {
    Swal.fire({
        title: "Are you sure?",
        text: "You will not be able to recover this task!",
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#d33",
        cancelButtonColor: "#6c757d",
        confirmButtonText: "Yes, delete it!"
    }).then((result) => {
        if (result.isConfirmed) {
            fetch(`./database/delete-task.php`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `tasks_id=${taskId}`,
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    Swal.fire({
                        icon: "success",
                        title: "Deleted!",
                        text: data.message,
                        timer: 1500,
                        showConfirmButton: false
                    }).then(() => {
                        location.reload();
                    });
                } else {
                    Swal.fire({
                        icon: "error",
                        title: "Error",
                        text: data.message || "Something went wrong."
                    });
                }
            })
            .catch(error => {
                console.error("Error:", error);
                Swal.fire({
                    icon: "error",
                    title: "Error",
                    text: "Failed to delete the task."
                });
            });
        }
    });
}

// delete category
    function deleteCategory(categoryId) {
    Swal.fire({
        title: "Are you sure?",
        text: "You will not be able to recover this category!",
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#d33",
        cancelButtonColor: "#6c757d",
        confirmButtonText: "Yes, delete it!"
    }).then((result) => {
        if (result.isConfirmed) {
            fetch(`./database/delete-category.php`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `category_id=${categoryId}`,
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    Swal.fire({
                        icon: "success",
                        title: "Deleted!",
                        text: data.message,
                        timer: 1500,
                        showConfirmButton: false
                    }).then(() => {
                        location.reload();
                    });
                } else {
                    Swal.fire({
                        icon: "error",
                        title: "Error",
                        text: data.message || "Something went wrong."
                    });
                }
            })
            .catch(error => {
                console.error("Error:", error);
                Swal.fire({
                    icon: "error",
                    title: "Error",
                    text: "Failed to delete the category."
                });
            });
        }
    });
}

// check for hash tab 
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

/* Ajax reload */

document.addEventListener('DOMContentLoaded', () => {
    function ajaxPaginate(e) {
        e.preventDefault();
        const url = this.ref;

        fetch(url, {
            headers: {
                'X-requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.text())
        .then(html => {
            const parser = new DOMParser();
            const doc = parser.parseFromString(html, 'text/html');
            const newContent = doc.querySelector('#taskList');
            const oldContent = doc.querySelector('#taskList');
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
        fetch(location.href, {headers: { 'X-Requested-With': 'XMLHttpRequest'} })
            .then(response => response.text())
            .then(html => {
                const parser = new DOMParser();
                const doc = parser.parseFromString(html, 'text/html');
                const newContent = doc.querySelector('#taskList');
                const oldContent = document.querySelector('#taskList');
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
    ajaxPaginate.call(this, e, '#catList', '#catList', 'completed');
  }

  function handlePendingClick(e) {
    ajaxPaginate.call(this, e, '#taskList', '#taskList', 'pending');
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

      const newCompleted = doc.querySelector('#taskList');
      const oldCompleted = document.querySelector('#taskList');
      if (newCompleted && oldCompleted) {
        oldCompleted.innerHTML = newCompleted.innerHTML;
      }

      const newPending = doc.querySelector('#catList');
      const oldPending = document.querySelector('#catList');
      if (newPending && oldPending) {
        oldPending.innerHTML = newPending.innerHTML;
      }

      attachPaginationListeners();
    });
  });
});
