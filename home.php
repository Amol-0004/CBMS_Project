<?php require_once 'config.php'; ?>
<?php include 'header.php'; ?>

<style>
    body {
        background: linear-gradient(rgba(0,0,0,0.4), rgba(0,0,0,0.4)), url('./assets/img/bus.jpg') center/cover no-repeat fixed;
        min-height: 100vh;
        display: flex;
        align-items: center;
        justify-content: center;
        font-family: Arial, sans-serif;
    }
    .home-container { max-width: 600px; width: 100%; margin: 1rem; text-align: center; }
    .home-card { background: rgba(255,255,255,0.9); border-radius: 15px; padding: 2rem; box-shadow: 0 8px 32px rgba(0,0,0,0.3); }
    .btn-custom { padding: 12px 30px; font-size: 1.2rem; margin: 1rem; border-radius: 50px; transition: transform 0.2s; }
    .btn-admin { background: linear-gradient(45deg, #007bff, #0056b3); border: none; color: white; }
    .btn-student { background: linear-gradient(45deg, #28a745, #1e7e34); border: none; color: white; }
    .btn-custom:hover { transform: scale(1.05); color: white; text-decoration: none; }
    .navbar { display: none; }  /* Hide nav on home */
</style>
<h1 style="text-align:center;color:blue;"> Dr.DYPIMED</h1>
<div class="home-container">
    <div class="home-card">
        <h1 class="text-primary mb-4"><i class="fas fa-bus"></i> College Bus Management System</h1>
        <p class="lead mb-5">Welcome! Choose your role to get started.</p>
        
        <?php if (isset($_SESSION['admin_logged_in'])): ?>
            <div class="alert alert-success">
                <h4>Admin Logged In</h4>
                <a href="admin_dashboard.php" class="btn btn-primary btn-lg">Go to Admin Dashboard</a>
            </div>
        <?php elseif (isset($_SESSION['student_logged_in'])): ?>
            <div class="alert alert-info">
                <h4>Student Logged In: <?php echo htmlspecialchars($_SESSION['student_name']); ?></h4>
                <a href="student_dashboard.php" class="btn btn-success btn-lg">Go to Student Dashboard</a>
            </div>
        <?php else: ?>
            <div class="row justify-content-center">
                <div class="col-md-6">
                    <a href="login.php" class="btn btn-admin btn-custom w-100 mb-3">
                        <i class="fas fa-user-shield"></i> Admin Login
                    </a>
                </div>
                <div class="col-md-6">
                    <a href="student_login.php" class="btn btn-student btn-custom w-100 mb-3">
                        <i class="fas fa-user-graduate"></i> Student Portal (Login/Register)
                    </a>
                </div>
            </div>
            <p class="mt-4 small text-muted">New students? Use the Student Portal to register first.</p>
        <?php endif; ?>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>