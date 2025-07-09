<?php
session_start();
require_once __DIR__ . '/../../../vendor/autoload.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Handle both JSON and form data
    $input = json_decode(file_get_contents('php://input'), true);
    if ($input) {
        $email = $input['email'] ?? '';
        $password = $input['password'] ?? '';
    } else {
        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';
    }

    try {
        // Create MongoDB connection
        $client = new MongoDB\Client("mongodb://localhost:27017");
        $database = $client->LMS;
        $usersCollection = $database->users;

        // Find user by email
        $user = $usersCollection->findOne(['email' => $email]);
        
        if ($user) {
            if (password_verify($password, $user['password'])) {
                // Set session variables
                $_SESSION['user_id'] = (string)$user['_id'];
                $_SESSION['role'] = $user['role'];
                $_SESSION['name'] = $user['name'];
                $_SESSION['email'] = $user['email'];

                // Redirect based on role
                switch ($user['role']) {
                    case 'Teacher':
                        header("Location: ../../dashboards/teacher_dashboard.php");
                        break;
                    case 'Student':
                        header("Location: ../../dashboards/student_dashboard.php");
                        break;
                    case 'Parent':
                        header("Location: ../../dashboards/parent_dashboard.php");
                        break;
                    default:
                        header("Location: ../../index.html");
                        break;
                }
                exit();
            } else {
                $error_message = "Invalid email or password!";
            }
        } else {
            $error_message = "User not found!";
        }
        
    } catch (Exception $e) {
        $error_message = "Error: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - EduConnect LMS</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #333;
        }

        .login-container {
            background: white;
            border-radius: 20px;
            padding: 3rem;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 400px;
            position: relative;
            overflow: hidden;
        }

        .login-container::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 5px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }

        .logo {
            text-align: center;
            margin-bottom: 2rem;
        }

        .logo h1 {
            color: #667eea;
            font-size: 2rem;
            margin-bottom: 0.5rem;
        }

        .logo p {
            color: #666;
            font-size: 0.9rem;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            color: #333;
            font-weight: 500;
        }

        .form-group input {
            width: 100%;
            padding: 0.75rem 1rem;
            border: 2px solid #e0e0e0;
            border-radius: 10px;
            font-size: 1rem;
            transition: border-color 0.3s ease;
        }

        .form-group input:focus {
            outline: none;
            border-color: #667eea;
        }

        .login-btn {
            width: 100%;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 0.75rem;
            border: none;
            border-radius: 10px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: transform 0.3s ease;
            margin-bottom: 1rem;
        }

        .login-btn:hover {
            transform: translateY(-2px);
        }

        .error-message {
            background: #fee;
            color: #c33;
            padding: 0.75rem;
            border-radius: 10px;
            margin-bottom: 1rem;
            text-align: center;
            border: 1px solid #fcc;
        }

        .links {
            text-align: center;
            margin-top: 1.5rem;
        }

        .links a {
            color: #667eea;
            text-decoration: none;
            margin: 0 1rem;
            transition: color 0.3s ease;
        }

        .links a:hover {
            color: #764ba2;
        }

        .divider {
            text-align: center;
            margin: 1rem 0;
            color: #666;
            position: relative;
        }

        .divider::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 0;
            right: 0;
            height: 1px;
            background: #e0e0e0;
            z-index: 1;
        }

        .divider span {
            background: white;
            padding: 0 1rem;
            position: relative;
            z-index: 2;
        }

        @media (max-width: 480px) {
            .login-container {
                margin: 1rem;
                padding: 2rem;
            }
        }
    </style>
</head>

<body>
    <div class="login-container">
        <div class="logo">
            <h1>EduConnect</h1>
            <p>Learning Management System</p>
        </div>

        <?php if (isset($error_message)): ?>
            <div class="error-message">
                <?php echo htmlspecialchars($error_message); ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="login.php">
            <div class="form-group">
                <label for="email">Email Address</label>
                <input type="email" id="email" name="email" required>
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required>
            </div>

            <button type="submit" class="login-btn">Sign In</button>
        </form>

        <div class="divider">
            <span>or</span>
        </div>

        <div class="links">
            <a href="register.php">Create Account</a>
            <a href="../../backend/index.html">Back to Home</a>
        </div>
    </div>
</body>

</html>
