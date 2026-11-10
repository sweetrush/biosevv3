<?php
define('CSS_CACHE_BUST', 3);
define('APP_VERSION', '1.0.0');
session_start();

// Redirect to index if already logged in
if (isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

$error = '';

// Handle login form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_once 'api/config.php';

    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($username) || empty($password)) {
        $error = 'Please enter both username and password.';
    } else {
        try {
            $pdo = getDBConnection();
            $stmt = $pdo->prepare("SELECT user_id, username, password_hash, first_name, last_name, email, access_level, department, is_active FROM users WHERE username = ? AND is_active = 1");
            $stmt->execute([$username]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user && password_verify($password, $user['password_hash'])) {
                $_SESSION['user_id'] = $user['user_id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['first_name'] = $user['first_name'];
                $_SESSION['last_name'] = $user['last_name'];
                $_SESSION['email'] = $user['email'];
                $_SESSION['access_level'] = $user['access_level'];
                $_SESSION['department'] = $user['department'];

                session_regenerate_id(true);

                header('Location: index.php');
                exit;
            } else {
                $error = 'Invalid username or password.';
            }
        } catch (PDOException $e) {
            error_log("Login error: " . $e->getMessage());
            $error = 'Database error. Please try again later.';
        } catch (Exception $e) {
            error_log("Login error: " . $e->getMessage());
            $error = 'An error occurred. Please try again.';
        }
    }
}

$flash_message = $_SESSION['flash_message'] ?? '';
unset($_SESSION['flash_message']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Samoa Biosecurity System - Secure Portal Access">
    <meta name="theme-color" content="#1e293b">
    <title>Login - Samoa Biosecurity System</title>
    <link rel="stylesheet" href="styles.css?v=<?php echo CSS_CACHE_BUST; ?>">
    <style>
        .login-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 50%, #334155 100%);
            padding: 20px;
            position: relative;
        }

        .login-container::before {
            content: '';
            position: absolute;
            top: 0; left: 0; right: 0; bottom: 0;
            background: radial-gradient(ellipse at top right, rgba(79, 109, 245, 0.08) 0%, transparent 50%),
                        radial-gradient(ellipse at bottom left, rgba(5, 150, 105, 0.05) 0%, transparent 50%);
        }

        .login-box {
            background: white;
            padding: 40px;
            border-radius: 16px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
            width: 100%;
            max-width: 420px;
            position: relative;
            z-index: 1;
        }

        .login-header {
            text-align: center;
            margin-bottom: 32px;
        }

        .login-header .logo {
            font-size: 2.5em;
            margin-bottom: 8px;
        }

        .login-header h1 {
            color: var(--text-primary);
            font-size: 1.5em;
            margin-bottom: 4px;
            font-weight: 700;
            letter-spacing: -0.5px;
        }

        .login-header p {
            color: var(--text-secondary);
            font-size: 0.9em;
            font-weight: 400;
        }

        .login-form .form-group {
            margin-bottom: 20px;
        }

        .login-form .form-group label {
            display: block;
            margin-bottom: 6px;
            font-weight: 600;
            color: var(--text-primary);
            font-size: 0.875em;
        }

        .login-form .form-group input {
            width: 100%;
            padding: 12px 16px;
            border: 1px solid var(--border-color);
            border-radius: 8px;
            font-size: 0.95em;
            transition: var(--transition);
            box-sizing: border-box;
            font-family: inherit;
            color: var(--text-primary);
        }

        .login-form .form-group input:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(79, 109, 245, 0.12);
        }

        .error-message {
            background: var(--error-light);
            color: #991b1b;
            padding: 12px 16px;
            border-radius: 8px;
            margin-bottom: 20px;
            border-left: 4px solid var(--error);
            font-size: 0.9em;
            font-weight: 500;
        }

        .flash-message {
            background: var(--success-light);
            color: #065f46;
            padding: 12px 16px;
            border-radius: 8px;
            margin-bottom: 20px;
            border-left: 4px solid var(--success);
            font-size: 0.9em;
            font-weight: 500;
        }

        .btn-login {
            width: 100%;
            padding: 12px;
            background: var(--primary);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 1em;
            font-weight: 600;
            cursor: pointer;
            transition: var(--transition);
            font-family: inherit;
        }

        .btn-login:hover {
            background: var(--primary-dark);
            box-shadow: 0 4px 12px rgba(79, 109, 245, 0.3);
            transform: translateY(-1px);
        }

        .btn-login:active {
            transform: translateY(0);
        }

        .login-footer {
            margin-top: 28px;
            text-align: center;
            padding-top: 20px;
            border-top: 1px solid var(--border-color);
            color: var(--text-muted);
            font-size: 0.8em;
            line-height: 1.8;
        }

        .login-footer .version-info {
            color: var(--text-muted);
            font-size: 0.85em;
            opacity: 0.7;
        }

        @media (max-width: 480px) {
            .login-box {
                padding: 30px 20px;
            }

            .login-header h1 {
                font-size: 1.3em;
            }

            .login-form .form-group input {
                padding: 14px;
                font-size: 16px;
            }

            .btn-login {
                padding: 14px;
            }
        }

        @media (hover: none) and (pointer: coarse) {
            .btn-login,
            .login-form .form-group input {
                min-height: 48px;
            }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-box">
            <div class="login-header">
                <div class="logo">&#x1F6A2;</div>
                <h1>Samoa Biosecurity</h1>
                <p>Secure Portal Access</p>
            </div>

            <?php if ($error): ?>
                <div class="error-message">
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <?php if ($flash_message): ?>
                <div class="flash-message">
                    <?php echo htmlspecialchars($flash_message); ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="" class="login-form">
                <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text" id="username" name="username" required
                           value="<?php echo htmlspecialchars($username ?? ''); ?>"
                           placeholder="Enter your username">
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" required
                           placeholder="Enter your password">
                </div>

                <button type="submit" class="btn-login">Sign In</button>
            </form>

            <div class="login-footer">
                <p>&copy; <?php echo date('Y'); ?> Samoa Biosecurity Authority</p>
                <p>All activities are logged and monitored</p>
                <p class="version-info">Version <?php echo APP_VERSION; ?></p>
            </div>
        </div>
    </div>

    <script>
        document.getElementById('username').focus();

        document.addEventListener('DOMContentLoaded', function() {
            const form = document.querySelector('form');
            const submitBtn = document.querySelector('.btn-login');

            form.addEventListener('submit', function() {
                submitBtn.disabled = true;
                submitBtn.textContent = 'Signing In...';
                submitBtn.style.opacity = '0.7';
            });
        });
    </script>
</body>
</html>
