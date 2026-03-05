<?php require_once 'config.php'; ?>
<?php include 'header.php'; ?>

<style>
    body {
        background: linear-gradient(rgba(0,0,0,0.5), rgba(0,0,0,0.5)), url('./assets/img/bus.jpg') center/cover no-repeat fixed;
        height: 100vh;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .login-card { max-width: 400px; width: 100%; margin: 1rem; }
</style>
<div class="login-card card shadow-lg">
    <div class="card-header bg-primary text-white text-center">
        <h4><i class="fas fa-lock"></i> Admin Login</h4>
    </div>
    <div class="card-body">
        <form id="login-frm" novalidate>
            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
            <div class="mb-3">
                <label for="username" class="form-label"><strong>Username</strong></label>
                <input type="text" id="username" name="username" class="form-control" required maxlength="50">
            </div>
            <div class="mb-3">
                <label for="password" class="form-label"><strong>Password</strong></label>
                <input type="password" id="password" name="password" class="form-control" required maxlength="100">
            </div>
            <button type="submit" class="btn btn-primary w-100" id="login-btn">
                <span class="spinner-border spinner-border-sm d-none" role="status"></span> Login
            </button>
        </form>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script>
$(document).ready(function() {
    // Temporarily disable HTTPS redirect for local dev
    // if (location.protocol !== 'https:') location.href = 'https:' + window.location.href.substring(window.location.protocol.length);

    $('#login-frm').on('submit', function(e) {
        e.preventDefault();
        let btn = $('#login-btn');
        let spinner = btn.find('.spinner-border');
        let isValid = true;

        // Basic validation
        if ($('#username').val().trim().length < 3) { alert('Username too short.'); isValid = false; }
        if ($('#password').val().length < 6) { alert('Password too short.'); isValid = false; }
        if (!isValid) return;

        spinner.removeClass('d-none');
        btn.prop('disabled', true).text('Logging in...');

        $.ajax({
            url: 'login_auth.php',
            method: 'POST',
            data: $(this).serialize(),
            dataType: 'json',
            success: function(resp) {
                spinner.addClass('d-none');
                btn.prop('disabled', false).text('Login');
                if (resp.success) {
                    location.href = 'index.php?page=home';
                } else {
                    alert(resp.message || 'Login failed.');
                }
            },
           error: function(xhr, status, err) {
    console.error('AJAX Error Details:', {
        status: status,
        statusCode: xhr.status,
        responseText: xhr.responseText,
        error: err
    });
    let msg = 'Network error. ';
    if (xhr.status === 404) msg += 'File not found (check login_auth.php path).';
    else if (xhr.status === 500) msg += 'Server error (check PHP logs).';
    else if (status === 'abort') msg += 'Request cancelled.';
    else msg += 'Check browser console (F12) for details.';
    spinner.addClass('d-none');
    btn.prop('disabled', false).text('Login');
    alert(msg);
}
        });
    });
});
</script>
</body>
</html>