<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>StudentHub - Asynchronous Event Management</title>
    <link rel="stylesheet" href="../css/style.css">
    <style>
        .alert-box { padding: 12px 15px; border-radius: 6px; margin-bottom: 20px; font-weight: 600; display: none; }
        .alert-success { background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .alert-error { background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        .admin-table { width: 100%; border-collapse: collapse; margin-top: 15px; font-size: 0.9rem; }
        .admin-table th, .admin-table td { padding: 12px 15px; border-bottom: 1px solid var(--border-color); text-align: left; }
        .admin-table th { background-color: var(--bg-body); color: var(--text-muted); text-transform: uppercase; font-size: 0.85rem; }
        .btn-action { padding: 6px 12px; border-radius: 4px; font-size: 0.8rem; font-weight: bold; cursor: pointer; border: none; margin-right: 5px; }
        .btn-edit { background-color: #ffc107; color: #000; }
        .btn-delete { background-color: #dc3545; color: #fff; }
    </style>
</head>
<body>
    <div class="layout">
        <aside id="sidebar">
            <div class="logo"><h2>STUDENTHUB</h2><p>Admin Portal</p></div>
            <nav>
                <a href="../admin.php">Admin Panel</a>
                <a href="ajax-events.php" class="active">Async Events API</a>
                <a href="../dashboard.php">Dashboard</a>
                <a href="../logout.php">Logout</a>
            </nav>
        </aside>

        <main>
            <h1>StudentHub — Event Management</h1>
            <p class="subtitle">Asynchronous CRUD operations using PHP JSON API and JavaScript Fetch API[cite: 18].</p>

            <!-- Dynamic Alert Box -->
            <div id="alertBox" class="alert-box"></div>

            <!-- Add / Edit Form Card -->
            <div class="card" style="margin-bottom: 30px;">
                <h3 id="formTitle">Add New Event</h3>
                <form id="eventForm" style="margin-top: 15px;">
                    <input type="hidden" id="event_id" name="event_id">
                    <input type="hidden" id="actionType" name="action" value="add">

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                        <div class="form-group">
                            <label>Event Title *</label>
                            <input type="text" id="title" name="title" required>
                        </div>
                        <div class="form-group">
                            <label>Category *</label>
                            <input type="text" id="category" name="category" placeholder="Workshop, Festival, Sports" required>
                        </div>
                        <div class="form-group">
                            <label>Event Date *</label>
                            <input type="date" id="event_date" name="event_date" required>
                        </div>
                        <div class="form-group">
                            <label>Status *</label>
                            <select id="status" name="status">
                                <option value="Open">Open</option>
                                <option value="Closed">Closed</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Description *</label>
                        <textarea id="description" name="description" rows="4" required></textarea>
                    </div>

                    <button type="submit" id="submitBtn" class="btn" style="width: auto;">Add Event</button>
                    <button type="button" id="cancelBtn" class="btn" style="width: auto; background: #6c757d; display: none; margin-left: 10px;">Cancel Edit</button>
                </form>
            </div>

            <!-- Event List Card -->
            <div class="card">
                <h3>Event List (Asynchronous Fetch API)</h3>
                <div style="overflow-x: auto;">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Title</th>
                                <th>Category</th>
                                <th>Date</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="eventTableBody">
                            <tr><td colspan="6" style="text-align: center; color: var(--text-muted);">Loading events...</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>

    <script>
        const apiUrl = '../api/events_api.php';
        const alertBox = document.getElementById('alertBox');
        const eventForm = document.getElementById('eventForm');
        const eventTableBody = document.getElementById('eventTableBody');
        const formTitle = document.getElementById('formTitle');
        const submitBtn = document.getElementById('submitBtn');
        const cancelBtn = document.getElementById('cancelBtn');

        function showAlert(message, type) {
            alertBox.textContent = message;
            alertBox.className = `alert-box ${type === 'success' ? 'alert-success' : 'alert-error'}`;
            alertBox.style.display = 'block';
            setTimeout(() => { alertBox.style.display = 'none'; }, 4000);
        }

        // 1. READ / LIST Events (GET)
        function fetchEvents() {
            fetch(apiUrl)
                .then(res => res.json())
                .then(response => {
                    if (response.status) {
                        showAlert(response.message, 'success');
                        let rows = '';
                        if (response.data.length === 0) {
                            rows = `<tr><td colspan="6" style="text-align:center;">No events found.</td></tr>`;
                        } else {
                            response.data.forEach(ev => {
                                rows += `
                                    <tr>
                                        <td>#${ev.event_id}</td>
                                        <td><strong>${ev.title}</strong></td>
                                        <td><span class="tag event">${ev.category}</span></td>
                                        <td>${ev.event_date}</td>
                                        <td>${ev.status}</td>
                                        <td>
                                            <button class="btn-action btn-edit" onclick='editEvent(${JSON.stringify(ev)})'>Edit</button>
                                            <button class="btn-action btn-delete" onclick='deleteEvent(${ev.event_id})'>Delete</button>
                                        </td>
                                    </tr>
                                `;
                            });
                        }
                        eventTableBody.innerHTML = rows;
                    } else {
                        showAlert('Failed to load events.', 'error');
                    }
                })
                .catch(err => {
                    console.error(err);
                    showAlert('Network error while fetching events.', 'error');
                });
        }

        // 2. CREATE & UPDATE (POST)
        eventForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const payload = {
                action: document.getElementById('actionType').value,
                event_id: document.getElementById('event_id').value,
                title: document.getElementById('title').value,
                category: document.getElementById('category').value,
                event_date: document.getElementById('event_date').value,
                description: document.getElementById('description').value,
                status: document.getElementById('status').value
            };

            fetch(apiUrl, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(payload)
            })
            .then(res => res.json())
            .then(response => {
                showAlert(response.message, response.status ? 'success' : 'error');
                if (response.status) {
                    resetForm();
                    fetchEvents();
                }
            })
            .catch(err => {
                console.error(err);
                showAlert('Operation failed.', 'error');
            });
        });

        // Populate Form for Editing
        window.editEvent = function(ev) {
            formTitle.textContent = 'Update Event';
            submitBtn.textContent = 'Update Event';
            cancelBtn.style.display = 'inline-block';
            document.getElementById('actionType').value = 'update';
            document.getElementById('event_id').value = ev.event_id;
            document.getElementById('title').value = ev.title;
            document.getElementById('category').value = ev.category;
            document.getElementById('event_date').value = ev.event_date;
            document.getElementById('description').value = ev.description;
            document.getElementById('status').value = ev.status;
            window.scrollTo({ top: 0, behavior: 'smooth' });
        };

        cancelBtn.addEventListener('click', resetForm);

        function resetForm() {
            eventForm.reset();
            formTitle.textContent = 'Add New Event';
            submitBtn.textContent = 'Add Event';
            cancelBtn.style.display = 'none';
            document.getElementById('actionType').value = 'add';
            document.getElementById('event_id').value = '';
        }

        // 3. DELETE Event (POST)
        window.deleteEvent = function(id) {
            if (confirm('Are you sure you want to delete this event?')) {
                fetch(apiUrl, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ action: 'delete', event_id: id })
                })
                .then(res => res.json())
                .then(response => {
                    showAlert(response.message, response.status ? 'success' : 'error');
                    if (response.status) {
                        fetchEvents();
                    }
                })
                .catch(err => {
                    console.error(err);
                    showAlert('Delete operation failed.', 'error');
                });
            }
        };

        // Load events on initial page load
        fetchEvents();
    </script>
</body>
</html>