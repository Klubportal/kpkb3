<?php

require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make('Illuminate\Contracts\Console\Kernel');
$kernel->bootstrap();

// Force Central
config(['database.default' => 'mysql']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    $user = \App\Models\Central\User::where('email', $email)->first();

    if ($user && \Illuminate\Support\Facades\Hash::check($password, $user->password)) {
        // Login
        \Illuminate\Support\Facades\Auth::guard('web')->login($user);

        // Redirect to Filament
        header('Location: /admin');
        exit;
    } else {
        $error = 'Ungültige Anmeldedaten!';
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Central Admin Login</title>
    <style>
        body { font-family: sans-serif; display: flex; justify-content: center; align-items: center; min-height: 100vh; background: #f3f4f6; }
        .login-box { background: white; padding: 2rem; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); width: 300px; }
        h1 { margin: 0 0 1.5rem; font-size: 1.5rem; color: #1f2937; }
        input { width: 100%; padding: 0.75rem; margin-bottom: 1rem; border: 1px solid #d1d5db; border-radius: 4px; box-sizing: border-box; }
        button { width: 100%; padding: 0.75rem; background: #3b82f6; color: white; border: none; border-radius: 4px; cursor: pointer; font-size: 1rem; }
        button:hover { background: #2563eb; }
        .error { color: #dc2626; margin-bottom: 1rem; padding: 0.5rem; background: #fee2e2; border-radius: 4px; font-size: 0.875rem; }
    </style>
</head>
<body>
    <div class="login-box">
        <h1>🎯 Central Admin</h1>
        <?php if (isset($error)): ?>
            <div class="error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        <form method="POST">
            <input type="email" name="email" placeholder="E-Mail" value="info@klubportal.com" required autofocus>
            <input type="password" name="password" placeholder="Passwort" value="Zagreb123!" required>
            <button type="submit">Anmelden</button>
        </form>
        <p style="margin-top: 1rem; font-size: 0.875rem; color: #6b7280;">
            Test: info@klubportal.com / Zagreb123!
        </p>
    </div>
</body>
</html>
