
<?php
require 'db_connect.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $title = trim($_POST['request_title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $location = trim($_POST['location'] ?? '');
    $requested_by = trim($_POST['requested_by'] ?? '');
    $priority = $_POST['priority'] ?? 'Medium';

    if ($title === '' || $description === '' ||
        $location === '' || $requested_by === '') {

        $error = "Please fill in all required fields.";

    } elseif (!in_array($priority, ['Low', 'Medium', 'High'], true)) {

        $error = "Invalid priority selected.";

    } else {

        $stmt = $pdo->prepare("
            INSERT INTO maintenance_requests
            (request_title, description, location, requested_by, priority)
            VALUES (?, ?, ?, ?, ?)
        ");

        $stmt->execute([
            $title,
            $description,
            $location,
            $requested_by,
            $priority
        ]);

        header("Location: index.php");
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Add Maintenance Request</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
          rel="stylesheet">

    <style>
        body {
            background: linear-gradient(135deg, #167D8D, #f5f9fc);
            font-family: 'Segoe UI', Arial, sans-serif;
            color: #243447;
            min-height: 100vh;
        }

        .container {
            max-width: 1100px;
        }

        .page-header {
            background: linear-gradient(135deg, #12304a, #176b87);
            color: white;
            padding: 30px 20px;
            border-radius: 15px;
            margin-bottom: 30px;
            box-shadow: 0 5px 18px rgba(18, 48, 74, 0.2);
        }

        .page-header h2 {
            font-weight: 700;
        }

        .page-header p {
            color: #d6eaf2;
            margin-bottom: 0;
        }

        .card {
            border: none;
            border-radius: 14px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
            overflow: hidden;
        }

        .card-header {
            background: linear-gradient(135deg, #12304a, #176b87);
            color: white;
            padding: 20px 25px;
            border: none;
        }

        .card-body {
            padding: 30px;
        }

        .form-label {
            font-weight: 600;
            color: #12304a;
        }

        .form-control,
        .form-select {
            border: 1px solid #ced9e2;
            border-radius: 8px;
            padding: 11px 14px;
        }

        .form-control:focus,
        .form-select:focus {
            border-color: #176b87;
            box-shadow: 0 0 0 0.2rem rgba(23, 107, 135, 0.15);
        }

        .btn-primary {
            background-color: #176b87;
            border-color: #176b87;
            font-weight: 600;
        }

        .btn-primary:hover {
            background-color: #124e63;
            border-color: #124e63;
        }

        .btn-secondary {
            font-weight: 600;
            border-radius: 7px;
        }

        .btn {
            padding: 10px 20px;
            border-radius: 7px;
            transition: 0.2s ease;
        }

        .btn:hover {
            transform: translateY(-1px);
        }

        .required {
            color: #dc3545;
        }

        @media (max-width: 768px) {
            .card-body {
                padding: 20px;
            }

            .page-header h2 {
                font-size: 24px;
            }
        }
    </style>
</head>

<body>

<div class="container py-5">

    <!-- Page Header -->
    <div class="page-header text-center">
        <h2>Add Maintenance Request</h2>
        <p>Submit a new maintenance request for processing</p>
    </div>

    <!-- Form Card -->
    <div class="card mx-auto" style="max-width: 750px;">

        <div class="card-header">
            <h4 class="mb-0">Request Information</h4>
        </div>

        <div class="card-body">

            <?php if ($error): ?>
                <div class="alert alert-danger">
                    <?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?>
                </div>
            <?php endif; ?>

            <form method="POST">

                <!-- Request Title -->
                <div class="mb-3">
                    <label class="form-label">
                        Request Title <span class="required">*</span>
                    </label>

                    <input type="text"
                           name="request_title"
                           class="form-control"
                           placeholder="Enter request title"
                           required>
                </div>

                <!-- Description -->
                <div class="mb-3">
                    <label class="form-label">
                        Description <span class="required">*</span>
                    </label>

                    <textarea name="description"
                              class="form-control"
                              rows="4"
                              placeholder="Describe the maintenance issue..."
                              required></textarea>
                </div>

                <!-- Location -->
                <div class="mb-3">
                    <label class="form-label">
                        Location <span class="required">*</span>
                    </label>

                    <input type="text"
                           name="location"
                           class="form-control"
                           placeholder="Enter location"
                           required>
                </div>

                <!-- Requested By -->
                <div class="mb-3">
                    <label class="form-label">
                        Requested By <span class="required">*</span>
                    </label>

                    <input type="text"
                           name="requested_by"
                           class="form-control"
                           placeholder="Enter your name"
                           required>
                </div>

                <!-- Priority -->
                <div class="mb-4">
                    <label class="form-label">Priority</label>

                    <select name="priority" class="form-select">
                        <option value="Low">Low</option>
                        <option value="Medium" selected>Medium</option>
                        <option value="High">High</option>
                    </select>
                </div>

                <!-- Buttons -->
                <div class="d-flex gap-2">

                    <button type="submit" class="btn btn-primary">
                        Submit Request
                    </button>

                    <a href="index.php" class="btn btn-secondary">
                        Cancel
                    </a>

                </div>

            </form>

        </div>
    </div>

    <!-- Footer -->
    <div class="text-center mt-4">
        <small class="text-muted">
            Maintenance Request Management System
        </small>
    </div>

</div>

</body>
</html>
