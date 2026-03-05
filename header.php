<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>College Bus Management System - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
    <style>
        body { background-color: #f8f9fa; }
        .navbar-brand i { color: #fff; }
    </style>
</head>
<body>
    <?php if (!in_array(basename($_SERVER['PHP_SELF']), ['login.php', 'student_login.php', 'student_register.php'])): ?>
    <!-- Nav here -->
<?php endif; ?>
    <?php if (basename($_SERVER['PHP_SELF']) !== 'login.php'): ?>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
    <div class="container-fluid">
        <?php if (isset($_SESSION['admin_logged_in'])): ?>
            <a class="navbar-brand" href="index.php"><i class="fas fa-bus"></i> CBMS Admin</a>
            <ul class="navbar-nav me-auto">
                <li class="nav-item"><a class="nav-link" href="index.php">Dashboard</a></li>
                <li class="nav-item"><a class="nav-link" href="manage_buses.php">Buses</a></li>
                <li class="nav-item"><a class="nav-link" href="manage_routes.php">Routes</a></li>
                <li class="nav-item"><a class="nav-link" href="manage_schedules.php">Schedules</a></li>
                <li class="nav-item"><a class="nav-link" href="manage_bookings.php">Bookings</a></li>
                <li class="nav-item"><a class="nav-link" href="reports.php">Reports</a></li>
            </ul>
            <ul class="navbar-nav">
                <li class="nav-item"><a class="nav-link" href="logout.php">Logout</a></li>
            </ul>
        <?php elseif (isset($_SESSION['student_logged_in'])): ?>
            <a class="navbar-brand" href="student_dashboard.php"><i class="fas fa-user-graduate"></i> Student Portal - <?php echo htmlspecialchars($_SESSION['student_name']); ?></a>
            <ul class="navbar-nav ms-auto">
                <li class="nav-item"><a class="nav-link" href="student_dashboard.php">Dashboard</a></li>
                <li class="nav-item"><a class="nav-link" href="book_bus.php">Book Bus</a></li>
                <li class="nav-item"><a class="nav-link" href="my_bookings.php">My Bookings</a></li>
                <li class="nav-item"><a class="nav-link" href="student_logout.php">Logout</a></li>
            </ul>
        <?php endif; ?>
    </div>
</nav>
    <?php endif; ?>
    <div class="container mt-4 mb-4">
        <?php
        // Display flash messages
        if (isset($_SESSION['flash'])) {
            $flash = $_SESSION['flash'];
            echo "<div class='alert alert-{$flash['type']} alert-dismissible fade show' role='alert'>{$flash['message']}<button type='button' class='btn-close' data-bs-dismiss='alert'></button></div>";
            unset($_SESSION['flash']);
        }
        ?>