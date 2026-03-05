<?php require_once 'config.php'; ?>
<?php include 'header.php'; ?>

<style>
    body { background: linear-gradient(rgba(0,0,0,0.4), rgba(0,0,0,0.4)), url('./assets/img/bus.jpg') center/cover no-repeat fixed; min-height: 100vh; display: flex; align-items: center; justify-content: center; }
    .reg-card { max-width: 400px; width: 100%; margin: 1rem; }
    .navbar { display: none; }  /* Hide nav on register */
</style>

<div class="reg-card card shadow-lg">
    <div class="card-header bg-success text-white text-center">
        <h4><i class="fas fa-user-plus"></i> Student Registration</h4>
    </div>
    <div class="card-body">
        <form id="reg-frm" novalidate>
            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
            <div class="mb-3">
                <label for="student_id" class="form-label"><strong>Student ID *</strong> (e.g., CS2025-001)</label>
                <input type="text" id="student_id" name="student_id" class="form-control" required maxlength="20">
            </div>
            <div class="mb-3">
                <label for="name" class="form-label"><strong>Full Name *</strong></label>
                <input type="text" id="name" name="name" class="form-control" required maxlength="100">
            </div>
            <div class="mb-3">
                <label for="email" class="form-label"><strong>Email *</strong></label>
                <input type="email" id="email" name="email" class="form-control" required maxlength="100">
            </div>
            <div class="mb-3">
                <label for="phone" class="form-label"><strong>Phone</strong></label>
                <input type="tel" id="phone" name="phone" class="form-control" maxlength="20">
            </div>
            <button type="submit" class="btn btn-success w-100" id="reg-btn">
                <span class="spinner-border spinner-border-sm d-none" role="status"></span> Register
            </button>
        </form>
        <div class="text-center mt-3">
            <a href="student_login.php">Already registered? Login</a>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
$(document).ready(function() {
    $('#reg-frm').on('submit', function(e) {
        e.preventDefault();
        let btn = $('#reg-btn'), spinner = btn.find('.spinner-border');
        let studentId = $('#student_id').val().trim(), name = $('#name').val().trim(), email = $('#email').val().trim();
        if (studentId.length < 5 || name.length < 2 || !email.includes('@')) { alert('Fill all fields correctly.'); return; }
        spinner.removeClass('d-none'); btn.prop('disabled', true).text('Registering...');
        $.ajax({
            url: 'student_register_auth.php', method: 'POST', data: $(this).serialize(), dataType: 'json',
            success: function(resp) {
                spinner.addClass('d-none'); btn.prop('disabled', false).text('Register');
                if (resp.success) { alert('Registered! Login now.'); location.href = 'student_login.php'; } else alert(resp.message);
            },
            error: function() { spinner.addClass('d-none'); btn.prop('disabled', false).text('Register'); alert('Network error.'); }
        });
    });
});
</script>
</body>
</html>