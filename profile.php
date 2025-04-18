<?php
    require_once "config.php";
    session_start();

    if (!isset($_SESSION['user'])) {
        header("Location: login.php");
        exit();
    }

    $username = $_SESSION['user'];
    $error = "";
    $success = "";

    $stmt = $conn->prepare("SELECT username, email FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->bind_result($current_username, $current_email);
    $stmt->fetch();
    $stmt->close();

    //update data
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $new_username = trim($_POST['username']);
        $new_email = trim($_POST['email']);
        $new_password = trim($_POST['password']);
        $confirm_password = trim($_POST['confirm_password']);
    
        if (!filter_var($new_email, FILTER_VALIDATE_EMAIL)) {
            $error = "Invalid email format!";
        } elseif ($new_password !== $confirm_password) {
            $error = "Passwords do not match!";
        } else {
            //siapkan password hash jika ada password baru
            $hashed_password = !empty($new_password) ? password_hash($new_password, PASSWORD_BCRYPT) : null;

        //SQL query untuk pembaruan
        $update_query = "UPDATE users SET username = ?, email = ?";
        $params = [$new_username, $new_email];
        $types = "ss";

        if ($hashed_password) {
            $update_query .= ", password = ?";
            $params[] = $hashed_password;
            $types .= "s";
        }

        $update_query .= " WHERE username = ?";
        $params[] = $username;
        $types .= "s";

        $stmt_update = $conn->prepare($update_query);
        $stmt_update->bind_param($types, ...$params);

        if ($stmt_update->execute()) {
            $_SESSION['user'] = $new_username; // Update username dalam session
            $success = "Profile updated successfully!";
            $current_username = $new_username;
            $current_email = $new_email;
        } else {
            $error = "Error updating profile.";
        }
        $stmt_update->close();
        }
    }
?>

<!DOCTYPE html>
    <html lang="en">
        <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Edit Profile</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">

        <style>
        body {
            background: linear-gradient(to right, #A85555, #A85555);
            height: 100vh;
            margin: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            font-family: Arial, sans-serif;
        }
        .container {
            background-color: #fff;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
            width: 100%;
            max-width: 1500px;
        }
        h2 {
            font-weight: bold;
            color: #333;
            text-align: center;
        }
        button {
            font-size: 1rem;
            padding: 10px 20px;
        }
        .btn-primary {
            background-color: #4facfe;
            border-color: #4facfe;
        }
        .btn-primary:hover {
            background-color: #00aaff;
            border-color: #00aaff;
        }
        </style>
        </head>

        <body>
            <div class="container mt-2">
                <h2><center>PROFILE</center></h2>

                <!--menampilkan pesan eror-->
                <?php if ($error): ?>
                <div class="alert alert-danger"><?= $error ?></div>
                <?php endif; ?>
                <!--menampilkan pesan sukses-->
                <?php if ($success): ?>
                <div class="alert alert-success"><?= $success ?></div>
                <?php endif; ?>

                <form method="POST" action="">
                    <div class="mb-3">
                        <label for="username" class="form-label">Username</label>
                        <input type="text" class="form-control" id="username" name="username" value="<?= htmlspecialchars($current_username); ?>" required>
                    </div>

                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" class="form-control" id="email" name="email" value="<?= htmlspecialchars($current_email); ?>" required>
                    </div>

                    <div class="mb-3">
                        <label for="password" class="form-label">New Password (Leave blank if not changing)</label>
                        <input type="password" class="form-control" id="password" name="password" placeholder="Enter new password">
                    </div>

                    <div class="mb-3">
                        <label for="confirm_password" class="form-label">Confirm New Password</label>
                        <input type="password" class="form-control" id="confirm_password" name="confirm_password" placeholder="Confirm new password">
                    </div>

                    <button type="submit" class="btn btn-primary">Update</button>
                    <a href="index.php" class="btn btn-secondary">Back</a>
                </form>
            </div>
        </body>
</html>