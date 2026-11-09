
<?php
require 'db_connect.php';

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$id || $id < 1) {
    header("Location: index.php");
    exit;
}

$stmt = $pdo->prepare("
    SELECT * FROM maintenance_requests WHERE id = ?
");
$stmt->execute([$id]);

$request = $stmt->fetch();

if (!$request) {
    header("Location: index.php");
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $title = trim($_POST['request_title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $location = trim($_POST['location'] ?? '');
    $requested_by = trim($_POST['requested_by'] ?? '');
    $priority = $_POST['priority'] ?? '';
    $status = $_POST['status'] ?? '';

    if (
        $title === '' ||
        $description === '' ||
        $location === '' ||
        $requested_by === ''
    ) {
        $error = "Please fill in all required fields.";

    } elseif (
        !in_array($priority, ['Low', 'Medium', 'High'], true) ||
        !in_array($status, ['Pending', 'In Progress', 'Completed'], true)
    ) {
        $error = "Invalid priority or status.";

    } else {

        $stmt = $pdo->prepare("
            UPDATE maintenance_requests
            SET request_title = ?,
                description = ?,
                location = ?,
                requested_by = ?,
                priority = ?,
                status = ?
            WHERE id = ?
        ");

        $stmt->execute([
            $title,
            $description,
            $location,
            $requested_by,
            $priority,
            $status,
            $id
        ]);

        header("Location: index.php");
        exit;
    }

    // Keep submitted values if validation fails
    $request['request_title'] = $title;
    $request['description'] = $description;
    $request['location'] = $location;
    $request['requested_by'] = $requested_by;
    $request['priority'] = $priority;
    $request['status'] = $status;
}

function e($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Edit Maintenance Request</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
          rel="stylesheet">

    <link rel="stylesheet" href="style.css">

    <style>
        body {
            min-height: 100vh;
            background: linear-gradient(135deg, #167D8D, #f5f9fc);
            font-family: 'Segoe UI', Arial, sans-serif;
            color: #243447;
        }

        /* Page Header */
        .page-header {
            background: linear-gradient(135deg, #12304a, #176b87);
            color: white;
            padding: 30px;
            border-radius: 16px;
            margin-bottom: 30px;
            box-shadow: 0 8px 25px rgba(16, 42, 67, 0.15);
            text-align: center;
        }

        .page-header h2 {
            font-weight: 700;
            margin-bottom: 8px;
        }

        .page-header p {
            margin-bottom: 0;
            color: #d6eaf2;
        }

        /* Form Card */
        .form-card {
            max-width: 750px;
            margin: auto;
            border: 1px solid #c5e0e7;
            border-radius: 18px;
            overflow: hidden;
            background: linear-gradient(135deg, #e8f4f7, #d8edf1);
            box-shadow: 0 10px 30px rgba(16, 42, 67, 0.15);
        }

        .form-card-header {
            background: linear-gradient(135deg, #12304a, #176b87);
            color: white;
            padding: 22px 28px;
        }

        .form-card-header h4 {
            margin: 0;
            font-weight: 600;
        }

        /* Teal-blue Form Background */
        .form-card-body {
            padding: 30px;
            background: linear-gradient(135deg, #e8f4f7, #d8edf1);
        }

        .form-label {
            font-weight: 600;
            color: #243b53;
            margin-bottom: 8px;
        }

        .form-control,
        .form-select {
            border: 1px solid #b9d2dc;
            border-radius: 10px;
            padding: 11px 14px;
            transition: 0.2s;
            background-color: #f5fbfc;
            color: #243447;
        }

        .form-control:focus,
        .form-select:focus {
            background-color: #ffffff;
            border-color: #176b87;
            box-shadow: 0 0 0 0.2rem rgba(23, 107, 135, 0.15);
        }

        textarea.form-control {
            resize: vertical;
        }

        /* Update Button */
        .btn-update {
            background: linear-gradient(135deg, #176b87, #124e63);
            color: white;
            border: none;
            border-radius: 10px;
            padding: 11px 22px;
            font-weight: 600;
            transition: 0.2s;
        }

        .btn-update:hover {
            background: #12304a;
            color: white;
            transform: translateY(-1px);
        }

        .btn-cancel {
            border-radius: 10px;
            padding: 11px 22px;
            font-weight: 600;
        }

        .form-footer {
            border-top: 1px solid #c5e0e7;
            margin-top: 25px;
            padding-top: 20px;
        }

        /* Responsive */
        @media (max-width: 576px) {
            .page-header {
                padding: 22px;
            }

            .form-card-body {
                padding: 20px;
            }
        }
    </style>
</head>

<body>

<div class="container py-5">

    <!-- Page Header -->
    <div class="page-header">
        <h2>Edit Maintenance Request</h2>
        <p>Update the details and status of your maintenance request.</p>
    </div>

    <!-- Edit Form -->
    <div class="card form-card">

        <div class="form-card-header">
            <h4>Request Information</h4>
        </div>

        <div class="form-card-body">

            <?php if ($error): ?>
                <div class="alert alert-danger rounded-3">
                    <?= e($error) ?>
                </div>
            <?php endif; ?>

            <form method="POST">

                <div class="mb-4">
                    <label class="form-label">Request Title</label>

                    <input
                        type="text"
                        name="request_title"
                        class="form-control"
                        value="<?= e($request['request_title']) ?>"
                        placeholder="Enter request title"
                        required
                    >
                </div>

                <div class="mb-4">
                    <label class="form-label">Description</label>

                    <textarea
                        name="description"
                        class="form-control"
                        rows="4"
                        placeholder="Describe the maintenance issue"
                        required><?= e($request['description']) ?></textarea>
                </div>

                <div class="mb-4">
                    <label class="form-label">Location</label>

                    <input
                        type="text"
                        name="location"
                        class="form-control"
                        value="<?= e($request['location']) ?>"
                        placeholder="Enter location"
                        required
                    >
                </div>

                <div class="mb-4">
                    <label class="form-label">Requested By</label>

                    <input
                        type="text"
                        name="requested_by"
                        class="form-control"
                        value="<?= e($request['requested_by']) ?>"
                        placeholder="Enter requester name"
                        required
                    >
                </div>

                <div class="row">

                    <div class="col-md-6 mb-4">
                        <label class="form-label">Priority</label>

                        <select name="priority" class="form-select" required>
                            <?php foreach (['Low', 'Medium', 'High'] as $p): ?>
                                <option value="<?= e($p) ?>"
                                    <?= $request['priority'] === $p ? 'selected' : '' ?>>
                                    <?= e($p) ?> Priority
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-md-6 mb-4">
                        <label class="form-label">Status</label>

                        <select name="status" class="form-select" required>
                            <?php foreach (['Pending', 'In Progress', 'Completed'] as $s): ?>
                                <option value="<?= e($s) ?>"
                                    <?= $request['status'] === $s ? 'selected' : '' ?>>
                                    <?= e($s) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                </div>

                <div class="form-footer d-flex flex-wrap gap-2">

                    <button type="submit" class="btn btn-update">
                        ✓ Update Request
                    </button>

                    <a href="index.php" class="btn btn-outline-secondary btn-cancel">
                        Cancel
                    </a>

                </div>

            </form>

        </div>
    </div>

    <!-- Footer -->
    <div class="text-center mt-4">
        <small style="color: #eaf6fa;">
            Maintenance Request Management System © 2026
        </small>
    </div>

</div>

</body>
</html>
```
