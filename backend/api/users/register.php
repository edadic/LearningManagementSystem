<?php
require_once __DIR__ . '/../../../vendor/autoload.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT);
    $role = $_POST['role'];

    try {
        // Create MongoDB connection
        $client = new MongoDB\Client("mongodb://localhost:27017");
        $database = $client->LMS;
        $usersCollection = $database->users;

        // Check if user already exists
        $existingUser = $usersCollection->findOne(['email' => $email]);
        
        if ($existingUser) {
            $error_message = "User with this email already exists!";
        } else {
            // Create user document
            $userDocument = [
                'name' => $name,
                'email' => $email,
                'password' => $password,
                'role' => $role,
                'created_at' => new MongoDB\BSON\UTCDateTime(),
                'updated_at' => new MongoDB\BSON\UTCDateTime()
            ];

            // Add role-specific fields
            switch ($role) {
                case 'Teacher':
                    $userDocument['teacher_data'] = [
                        'subjects' => [],
                        'classes' => []
                    ];
                    break;
                    
                case 'Student':
                    $userDocument['student_data'] = [
                        'grade' => '',
                        'class' => '',
                        'parent_id' => null
                    ];
                    break;
                    
                case 'Parent':
                    $userDocument['parent_data'] = [
                        'children' => []
                    ];
                    break;
            }
            
            $result = $usersCollection->insertOne($userDocument);
            
            if ($result->getInsertedCount() > 0) {
                $success_message = "Registration successful! You can now log in.";
            } else {
                $error_message = "Registration failed. Please try again.";
            }
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
    <title>Register - EduConnect LMS</title>
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
            padding: 2rem 0;
        }

        .register-container {
            background: white;
            border-radius: 20px;
            padding: 3rem;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 450px;
            position: relative;
            overflow: hidden;
        }

        .register-container::before {
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

        .form-group input,
        .form-group select {
            width: 100%;
            padding: 0.75rem 1rem;
            border: 2px solid #e0e0e0;
            border-radius: 10px;
            font-size: 1rem;
            transition: border-color 0.3s ease;
        }

        .form-group input:focus,
        .form-group select:focus {
            outline: none;
            border-color: #667eea;
        }

        .form-group select {
            background: white;
            cursor: pointer;
        }

        .register-btn {
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

        .register-btn:hover {
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

        .success-message {
            background: #efe;
            color: #3c3;
            padding: 0.75rem;
            border-radius: 10px;
            margin-bottom: 1rem;
            text-align: center;
            border: 1px solid #cfc;
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

        .role-icons {
            display: flex;
            justify-content: space-around;
            margin: 1rem 0;
            padding: 1rem;
            background: #f8f9ff;
            border-radius: 10px;
        }

        .role-icon {
            text-align: center;
            padding: 0.5rem;
        }

        .role-icon .icon {
            font-size: 2rem;
            margin-bottom: 0.25rem;
        }

        .role-icon .label {
            font-size: 0.8rem;
            color: #666;
        }

        @media (max-width: 480px) {
            .register-container {
                margin: 1rem;
                padding: 2rem;
            }

            .role-icons {
                flex-direction: column;
                gap: 0.5rem;
            }

            .role-icon {
                display: flex;
                align-items: center;
                justify-content: flex-start;
                text-align: left;
            }

            .role-icon .icon {
                margin-right: 1rem;
                margin-bottom: 0;
            }
        }
    </style>
</head>

<body>
    <div class="register-container">
        <div class="logo">
            <h1>Join EduConnect</h1>
            <p>Create your account to get started</p>
        </div>

        <?php if (isset($error_message)): ?>
            <div class="error-message">
                <?php echo htmlspecialchars($error_message); ?>
            </div>
        <?php endif; ?>

        <?php if (isset($success_message)): ?>
            <div class="success-message">
                <?php echo htmlspecialchars($success_message); ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="register.php">
            <div class="form-group">
                <label for="name">Full Name</label>
                <input type="text" id="name" name="name" required>
            </div>

            <div class="form-group">
                <label for="email">Email Address</label>
                <input type="email" id="email" name="email" required>
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required minlength="6">
            </div>

            <div class="form-group">
                <label for="role">I am a...</label>
                <select name="role" id="role" required>
                    <option value="">Select your role</option>
                    <option value="Student">Student</option>
                    <option value="Teacher">Teacher</option>
                    <option value="Parent">Parent</option>
                </select>
            </div>

            <div class="role-icons">
                <div class="role-icon">
                    <div class="icon">🎓</div>
                    <div class="label">Student</div>
                </div>
                <div class="role-icon">
                    <div class="icon">👩‍🏫</div>
                    <div class="label">Teacher</div>
                </div>
                <div class="role-icon">
                    <div class="icon">👨‍👩‍👧‍👦</div>
                    <div class="label">Parent</div>
                </div>
            </div>

            <button type="submit" class="register-btn">Create Account</button>
        </form>

        <div class="divider">
            <span>or</span>
        </div>

        <div class="links">
            <a href="login.php">Already have an account?</a>
            <a href="../../backend/index.html">Back to Home</a>
        </div>
    </div>
</body>

</html>
