<?php
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

            // Get user from database
            $stmt = $pdo->prepare("SELECT user_id, username, password_hash, first_name, last_name, email, access_level, department, is_active FROM users WHERE username = ? AND is_active = 1");
            $stmt->execute([$username]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user && password_verify($password, $user['password_hash'])) {
                // Login successful
                $_SESSION['user_id'] = $user['user_id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['first_name'] = $user['first_name'];
                $_SESSION['last_name'] = $user['last_name'];
                $_SESSION['email'] = $user['email'];
                $_SESSION['access_level'] = $user['access_level'];
                $_SESSION['department'] = $user['department'];

                // Regenerate session ID for security
                session_regenerate_id(true);

                // Redirect to dashboard
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

// Get any flash messages
$flash_message = $_SESSION['flash_message'] ?? '';
unset($_SESSION['flash_message']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Samoa Biosecurity System - Secure Portal Access">
    <meta name="theme-color" content="#667eea">
    <title>Login - Samoa Biosecurity System</title>
    <link rel="stylesheet" href="styles.css?v=<?php echo time(); ?>">
    <style>
        .login-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 15px;
            box-sizing: border-box;
        }

        .login-box {
            background: white;
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
            width: 100%;
            max-width: 400px;
            box-sizing: border-box;
        }

        .login-header {
            text-align: center;
            margin-bottom: 30px;
        }

        .login-header .logo {
            font-size: 3em;
            margin-bottom: 10px;
        }

        .login-header h1 {
            color: #2d3748;
            font-size: 1.8em;
            margin-bottom: 5px;
        }

        .login-header p {
            color: #718096;
            font-size: 0.95em;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #374151;
        }

        .form-group input {
            width: 100%;
            padding: 12px 16px;
            border: 2px solid #e2e8f0;
            border-radius: 8px;
            font-size: 1em;
            transition: all 0.3s ease;
            box-sizing: border-box;
        }

        .form-group input:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }

        .error-message {
            background: #fee2e2;
            color: #991b1b;
            padding: 12px 16px;
            border-radius: 8px;
            margin-bottom: 20px;
            border-left: 4px solid #dc2626;
        }

        .flash-message {
            background: #d1fae5;
            color: #065f46;
            padding: 12px 16px;
            border-radius: 8px;
            margin-bottom: 20px;
            border-left: 4px solid #10b981;
        }

        .btn-login {
            width: 100%;
            padding: 14px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 1.1em;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(102, 126, 234, 0.4);
        }

        .login-footer {
            margin-top: 30px;
            text-align: center;
            padding-top: 20px;
            border-top: 1px solid #e2e8f0;
            color: #718096;
            font-size: 0.9em;
        }


        /* Responsive Design */
        @media (max-width: 480px) {
            .login-box {
                padding: 30px 20px;
                max-width: 100%;
            }

            .login-header h1 {
                font-size: 1.5em;
            }

            .login-header p {
                font-size: 0.9em;
            }

            .form-group input {
                padding: 14px;
                font-size: 16px; /* Prevents zoom on iOS */
            }

            .btn-login {
                padding: 16px;
                font-size: 1em;
            }

            .default-credentials {
                padding: 12px;
                font-size: 0.85em;
            }

            .default-credentials h4 {
                font-size: 0.95em;
            }

            .default-credentials p {
                margin: 8px 0;
            }
        }

        @media (max-width: 360px) {
            .login-box {
                padding: 25px 15px;
            }

            .login-header .logo {
                font-size: 2.5em;
            }

            .login-header h1 {
                font-size: 1.3em;
            }

            .form-group input {
                padding: 12px;
            }

            .btn-login {
                padding: 14px;
            }
        }

        @media (min-width: 768px) {
            .login-box {
                max-width: 450px;
            }
        }

        @media (min-width: 1024px) {
            .login-box {
                max-width: 500px;
            }
        }

        /* Improve touch targets on mobile */
        @media (hover: none) and (pointer: coarse) {
            .btn-login,
            .form-group input {
                min-height: 48px;
            }
        }

        /* Landscape mode on mobile */
        @media (max-height: 500px) and (orientation: landscape) {
            .login-container {
                align-items: flex-start;
                padding: 10px;
            }

            .login-box {
                margin-top: 10px;
            }

            .login-header {
                margin-bottom: 20px;
            }

            .login-header .logo {
                font-size: 2em;
            }

            .default-credentials {
                margin-top: 15px;
            }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-box">
            <div class="login-header">
                <div class="logo">🚢</div>
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

            <form method="POST" action="">
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
            </div>
        </div>
    </div>

    <script>
        // Auto-focus username field
        document.getElementById('username').focus();

        // Mobile-optimized form handling
        document.addEventListener('DOMContentLoaded', function() {
            // Add enter key handling for better mobile UX
            const inputs = document.querySelectorAll('input[type="text"], input[type="password"]');
            inputs.forEach(function(input) {
                input.addEventListener('keypress', function(e) {
                    if (e.key === 'Enter') {
                        document.querySelector('.btn-login').click();
                    }
                });
            });

            // Handle form submission with loading state
            const form = document.querySelector('form');
            const submitBtn = document.querySelector('.btn-login');

            form.addEventListener('submit', function() {
                submitBtn.disabled = true;
                submitBtn.textContent = 'Signing In...';
                submitBtn.style.opacity = '0.7';
            });

            // Prevent zoom on iOS when focusing inputs
            inputs.forEach(function(input) {
                input.addEventListener('focus', function() {
                    if (window.innerWidth < 768) {
                        document.querySelector('meta[name="viewport"]').setAttribute('content', 'width=device-width, initial-scale=1, maximum-scale=1');
                    }
                });

                input.addEventListener('blur', function() {
                    document.querySelector('meta[name="viewport"]').setAttribute('content', 'width=device-width, initial-scale=1');
                });
            });

            // Handle orientation change
            window.addEventListener('orientationchange', function() {
                setTimeout(function() {
                    window.scrollTo(0, 0);
                }, 100);
            });

            // Add touch feedback for better mobile interaction
            const interactiveElements = document.querySelectorAll('input, button');
            interactiveElements.forEach(function(element) {
                element.addEventListener('touchstart', function() {
                    this.style.opacity = '0.8';
                });

                element.addEventListener('touchend', function() {
                    setTimeout(() => {
                        this.style.opacity = '1';
                    }, 150);
                });
            });
        });
    </script>
</body>
</html>
