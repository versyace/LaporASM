<?php require_once 'functions.php';
if (isAdmin()) { header("Location: admin.php"); exit; }
$flash = getFlash();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Admin - Sistem Pengaduan</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        :root {
            --primary: #c0001a;
            --accent:  #1a3a8f;
            --bg-page: #f5f6fa;
        }
        body { background-color: var(--bg-page); font-family: system-ui, sans-serif; }

        .header-bar {
            background: linear-gradient(135deg, var(--primary) 0%, #8b0010 40%, var(--accent) 100%);
            border-bottom: 3px solid #fff;
            padding: 1.1rem 0;
            margin-bottom: 2rem;
            box-shadow: 0 2px 12px rgba(26,58,143,0.18);
        }
        .header-title { font-size: 1.25rem; font-weight: 700; color: #fff; margin: 0; letter-spacing: 0.5px; }
        .header-sub   { font-size: 0.82rem; color: rgba(255,255,255,0.78); margin: 0; }

        .login-card {
            border-radius: 10px;
            border: 1px solid #dde3ef;
            box-shadow: 0 2px 8px rgba(26,58,143,0.08);
        }

        .btn-login {
            background: linear-gradient(90deg, var(--primary), var(--accent));
            border: none; border-radius: 7px; font-weight: 600;
        }
        .btn-login:hover { opacity: 0.9; }

        /* Toggle mata */
        .pw-wrapper { position: relative; }
        .pw-wrapper .form-control { padding-right: 2.8rem; }
        .pw-toggle {
            position: absolute; top: 50%; right: 0.75rem;
            transform: translateY(-50%);
            background: none; border: none; padding: 0;
            color: #64748b; cursor: pointer; font-size: 1.05rem;
            line-height: 1; transition: color 0.2s;
        }
        .pw-toggle:hover { color: var(--primary); }
    </style>
</head>
<body>

<header class="header-bar">
    <div class="container">
        <h1 class="header-title"><i class="bi bi-shield-lock me-2"></i>SISTEM PENGADUAN FASILITAS RUANGAN ASM</h1>
        <p class="header-sub">Halaman Masuk Administrator</p>
    </div>
</header>

<div class="d-flex align-items-center justify-content-center" style="min-height:70vh">
    <div class="card p-4 login-card" style="max-width:400px;width:100%">
        <div class="text-center mb-4">
            <div class="mb-2" style="font-size:2rem; color:var(--primary)"><i class="bi bi-person-lock"></i></div>
            <h5 class="fw-bold mb-0" style="color:var(--accent)">Login Administrator</h5>
        </div>

        <?php if($flash): ?>
            <div class="alert alert-danger d-flex align-items-center justify-content-between py-2 small mb-3">
                <span><i class="bi bi-exclamation-triangle me-1"></i><?= htmlspecialchars($flash['msg'] ?? $flash['message'] ?? '') ?></span>
                <button type="button" class="btn-close btn-close-sm ms-3 flex-shrink-0" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <form method="POST" action="proses.php">
            <input type="hidden" name="act" value="login">

            <div class="mb-3">
                <label class="form-label fw-medium small">Username</label>
                <div class="input-group">
                    <span class="input-group-text bg-white border-end-0"><i class="bi bi-person text-muted"></i></span>
                    <input type="text" name="username" class="form-control border-start-0" placeholder="Username" required autocomplete="username">
                </div>
            </div>

            <div class="mb-4">
                <label class="form-label fw-medium small">Password</label>
                <div class="pw-wrapper">
                    <div class="input-group">
                        <span class="input-group-text bg-white border-end-0"><i class="bi bi-lock text-muted"></i></span>
                        <input type="password" id="pwInput" name="password" class="form-control border-start-0" placeholder="Password" required autocomplete="current-password" style="padding-right:2.8rem">
                    </div>
                    <button type="button" class="pw-toggle" id="pwToggle" tabindex="-1">
                        <i class="bi bi-eye" id="pwIcon"></i>
                    </button>
                </div>
            </div>

            <button type="submit" class="btn btn-login btn-dark w-100 py-2">
                <i class="bi bi-box-arrow-in-right me-2"></i>Masuk
            </button>
        </form>

        <div class="text-center mt-3">
            <a href="index.php" class="text-muted small"><i class="bi bi-arrow-left me-1"></i>Kembali ke Form Pengaduan</a>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
const pwInput  = document.getElementById('pwInput');
const pwToggle = document.getElementById('pwToggle');
const pwIcon   = document.getElementById('pwIcon');
pwToggle.addEventListener('click', () => {
    const isHidden = pwInput.type === 'password';
    pwInput.type = isHidden ? 'text' : 'password';
    pwIcon.className = isHidden ? 'bi bi-eye-slash' : 'bi bi-eye';
});
</script>
</body>
</html>