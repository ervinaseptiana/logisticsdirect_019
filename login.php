<?php
require_once "config.php";
session_start();

// Tambahkan admin jika belum ada di database
$admin_username = "admin";
$admin_password = "Admin@0411";
$admin_role = "admin";

// Cek apakah admin sudah ada di database
$check_admin = $conn->prepare("SELECT id FROM users WHERE username = ? AND role = ?");
$check_admin->bind_param("ss", $admin_username, $admin_role);
$check_admin->execute();
$check_admin->store_result();

if ($check_admin->num_rows === 0) {
    // Jika admin belum ada, tambahkan ke database
    $hashed_password = password_hash($admin_password, PASSWORD_DEFAULT);
    $insert_admin = $conn->prepare("INSERT INTO users (username, password, role) VALUES (?, ?, ?)");
    $insert_admin->bind_param("sss", $admin_username, $hashed_password, $admin_role);
    $insert_admin->execute();
    $insert_admin->close();
}
$check_admin->close();

$error = "";

// Proses form login
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Periksa data dari database (hanya untuk admin)
    $stmt = $conn->prepare("SELECT password, role FROM users WHERE username = ? AND role = 'admin'");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->store_result();
    $stmt->bind_result($hashed_password, $role);

    if ($stmt->num_rows > 0) {
        $stmt->fetch();
        if (password_verify($password, $hashed_password)) {
            $_SESSION['user'] = $username;
            $_SESSION['role'] = $role;
            header("Location: index.php");
            exit();
        } else {
            $error = "Invalid username or password!";
        }
    } else {
        $error = "Access denied! Admins only.";
    }
    $stmt->close();
}
?>

<!DOCTYPE html>
    <html lang="en">
        <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Login</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
        <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
    </head>

    <body class="bg-light">
    <div class="container d-flex align-items-center justify-content-center" style="height: 100vh;">
        <div class="card shadow p-4" style="width: 300px;">
            <div style="display: flex; justify-content: center; margin-bottom: 20px;">
                <img src="asset/image/logo1.png" alt="Logo" style="width: 150px; height: auto;">
            </div>
            <h3 class="text-center mb-4" style="font-size: 0.9rem;">Sign in to your account</h3>
    
            <?php if ($error): ?>
                <div class="alert alert-danger"><?= $error ?></div>
            <?php endif; ?>

            <form method="POST" action="">
                <div class="mb-3">
                    <label for="username" class="form-label">Username</label>
                    <div class="input-group">
                        <span class="input-group-text">
                            <i class="fas fa-user"></i>
                        </span>
                        <input type="text" class="form-control" id="username" name="username" placeholder="Enter your username" required>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="password" class="form-label">Password</label>
                    <div class="input-group">
                        <span class="input-group-text">
                            <i class="fas fa-lock"></i>
                        </span>
                        <input type="password" class="form-control" id="password" name="password" placeholder="Enter your password" required>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary w-100">Login</button>
            </form>
        </div>
    </div>
    </body>
</html>