
<?php
require 'db_connect.php';

function e($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

function getRequests($pdo, $search = '') {
    if ($search !== '') {
        $stmt = $pdo->prepare("
            SELECT * FROM maintenance_requests
            WHERE request_title LIKE ? OR requested_by LIKE ?
            ORDER BY id DESC
        ");
        $term = "%$search%";
        $stmt->execute([$term, $term]);
    } else {
        $stmt = $pdo->query("
            SELECT * FROM maintenance_requests ORDER BY id DESC
        ");
    }

    return $stmt->fetchAll();
}

function renderRows($requests) {
    if (!$requests) {
        return '<tr><td colspan="8" class="text-center py-4">
            No maintenance requests found.</td></tr>';
    }

    foreach ($requests as $r) {
        $id = e($r['id']);
        $priority = e($r['priority']);
        $status = e($r['status']);

        $priorityClass = [
            'Low' => 'success', 'Medium' => 'warning',
            'High' => 'danger'
        ][$r['priority']] ?? 'secondary';

        $statusClass = [
            'Pending' => 'secondary', 'In Progress' => 'primary',
            'Completed' => 'success'
        ][$r['status']] ?? 'secondary';

        echo "<tr>
            <td>$id</td>
            <td><strong>" . e($r['request_title']) . "</strong><br>
                <small>" . e($r['description']) . "</small></td>
            <td>" . e($r['location']) . "</td>
            <td>" . e($r['requested_by']) . "</td>
            <td><span class='badge bg-$priorityClass'>$priority</span></td>
            <td><span class='badge bg-$statusClass'>$status</span></td>
            <td>" . e(date('M d, Y', strtotime($r['request_date']))) . "</td>
            <td class='text-nowrap'>
                <a href='edit.php?id=$id' class='btn btn-sm btn-warning'>Edit</a>
                <a href='delete.php?id=$id' class='btn btn-sm btn-danger'
                   onclick=\"return confirm('Delete this request?')\">Delete</a>
            </td>
        </tr>";
    }
}

// AJAX search request
if (isset($_GET['ajax'])) {
    renderRows(getRequests($pdo, trim($_GET['search'] ?? '')));
    exit;
}

$requests = getRequests($pdo, trim($_GET['search'] ?? ''));
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Maintenance Request System</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
      rel="stylesheet">

<style>
body {
    background: linear-gradient(135deg,#167D8D,#f5f9fc);
    font-family: 'Segoe UI',Arial,sans-serif;
    color: #243447;
    min-height: 100vh;
}
.container { max-width: 1400px; }
.page-header {
    background: linear-gradient(135deg,#12304a,#176b87);
    color: white; padding: 35px 20px; border-radius: 15px;
    margin-bottom: 30px; text-align: center;
    box-shadow: 0 5px 18px rgba(18,48,74,.2);
}
.page-header h2 { font-weight: 700; }
.page-header p { color: #d6eaf2; margin: 0; }
.card {
    background: linear-gradient(135deg,#e8f4f7,#d8edf1);
    border: 1px solid #c5e0e7; border-radius: 14px;
    box-shadow: 0 4px 15px rgba(18,48,74,.12);
}
.card-body { padding: 22px; }
.btn-primary { background: #176b87; border-color: #176b87; }
.btn-primary:hover { background: #124e63; border-color: #124e63; }
.btn-warning { background: #f4b942; border-color: #f4b942; }
.btn { border-radius: 7px; }
.form-control {
    border: 1px solid #b9d2dc; border-radius: 8px;
    padding: 10px 14px; background: #f8fcfd;
}
.table { margin: 0; }
.table thead th {
    background: #12304a; color: white; padding: 16px;
    border: none; white-space: nowrap;
}
.table tbody td { padding: 15px; border-color: #d2e5eb; }
.table tbody tr:nth-child(even) td { background: #e4f1f4; }
.table tbody tr:nth-child(odd) td { background: #edf7f9; }
.badge { padding: 8px 11px; }
small { color: #607d8b; }
@media(max-width:768px) {
    .page-header { padding: 25px 15px; }
    .page-header h2 { font-size: 24px; }
    .card-body { padding: 15px; }
}
</style>
</head>

<body>
<div class="container py-5">

    <div class="page-header">
        <h2>Maintenance Request System</h2>
        <p>Manage and track maintenance requests efficiently</p>
    </div>

    <div class="card mb-4">
        <div class="card-body">
            <div class="row g-2 align-items-center">
                <div class="col-md-2">
                    <a href="add.php" class="btn btn-primary w-100">
                        + Add Request
                    </a>
                </div>

                <div class="col-md-10">
                    <form id="searchForm" class="row g-2">
                        <div class="col-md-10">
                            <input type="text" id="searchInput"
                                   class="form-control"
                                   placeholder="Name who Request"
                                   value="<?= e($_GET['search'] ?? '') ?>">
                        </div>
                        <div class="col-md-2">
                            <button class="btn btn-primary w-100" id="searchBtn">
                                Search
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                    <tr>
                        <th>ID</th><th>Request Title</th><th>Location</th>
                        <th>Requested By</th><th>Priority</th><th>Status</th>
                        <th>Date</th><th>Actions</th>
                    </tr>
                    </thead>
                    <tbody id="requestTable">
                    <?php renderRows($requests); ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="text-center mt-4">
        <small>Maintenance Request Management System © 2026</small>
    </div>
</div>

<script>
document.getElementById('searchForm').addEventListener('submit', function(e) {
    e.preventDefault();

    const search = document.getElementById('searchInput').value;
    const button = document.getElementById('searchBtn');
    const table = document.getElementById('requestTable');

    button.disabled = true;
    button.textContent = 'Searching...';

    fetch('index.php?ajax=1&search=' + encodeURIComponent(search))
        .then(response => {
            if (!response.ok) throw new Error('Search failed');
            return response.text();
        })
        .then(data => table.innerHTML = data)
        .catch(() => {
            table.innerHTML = `<tr><td colspan="8" class="text-center text-danger">
                Search failed. Please try again.</td></tr>`;
        })
        .finally(() => {
            button.disabled = false;
            button.textContent = 'Search';
        });
});
</script>

</body>
</html>
