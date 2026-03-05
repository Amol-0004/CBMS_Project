<?php require_once 'config.php'; ?>
<?php include 'header.php'; ?>

<style>
    body { background: linear-gradient(rgba(0,0,0,0.4), rgba(0,0,0,0.4)), url('./assets/img/bus.jpg') center/cover no-repeat fixed; min-height: 100vh; display: flex; align-items: center; justify-content: center; }
    .login-card { max-width: 400px; width: 100%; margin: 1rem; }
    .navbar { display: none; }
</style>

<div class="login-card card shadow-lg">
    <div class="card-header bg-info text-white text-center">
        <h4><i class="fas fa-user-graduate"></i> Student Login</h4>
    </div>
    <div class="card-body">
        <form id="login-frm">
            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
            <div class="mb-3">
                <label for="student_id" class="form-label"><strong>Student ID *</strong></label>
                <input type="text" id="student_id" name="student_id" class="form-control" required maxlength="20">
            </div>
            <div class="mb-3">
                <label for="password" class="form-label"><strong>Password *</strong> (Temp: Use Student ID)</label>
                <input type="password" id="password" name="password" class="form-control" required maxlength="20">
            </div>
            <button type="submit" class="btn btn-info w-100" id="login-btn">
                <span class="spinner-border spinner-border-sm d-none" role="status"></span> Login
            </button>
        </form>
        <div class="text-center mt-3">
            <a href="student_register.php">New student? Register</a>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
$(document).ready(function() {
    $('#login-frm').on('submit', function(e) {
        e.preventDefault();
        let btn = $('#login-btn'), spinner = btn.find('.spinner-border');
        if ($('#student_id').val().trim().length < 5 || $('#password').val().length < 5) { alert('Invalid input.'); return; }
        spinner.removeClass('d-none'); btn.prop('disabled', true).text('Logging in...');
        $.ajax({
            url: 'student_login_auth.php', method: 'POST', data: $(this).serialize(), dataType: 'json',
            success: function(resp) {
                spinner.addClass('d-none'); btn.prop('disabled', false).text('Login');
                if (resp.success) location.href = 'student_dashboard.php'; else alert(resp.message);
            },
            error: function() { spinner.addClass('d-none'); btn.prop('disabled', false).text('Login'); alert('Network error.'); }
        });
    });
});
</script>
</body>
</html>