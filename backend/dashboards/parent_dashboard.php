<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'Parent') {
    header("Location: ../api/users/login.php");
    exit();
}

$userId = $_SESSION['user_id'];
$userName = isset($_SESSION['name']) ? $_SESSION['name'] : 'Parent';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Parent Dashboard - EduConnect LMS</title>
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
            color: #333;
        }
        
        .navbar {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            padding: 1rem 2rem;
            box-shadow: 0 2px 20px rgba(0, 0, 0, 0.1);
            position: sticky;
            top: 0;
            z-index: 100;
        }
        
        .nav-container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            max-width: 1200px;
            margin: 0 auto;
        }
        
        .logo {
            font-size: 1.5rem;
            font-weight: bold;
            color: #667eea;
        }
        
        .nav-links {
            display: flex;
            gap: 2rem;
            align-items: center;
        }
        
        .nav-links a, .nav-links button {
            text-decoration: none;
            color: #333;
            padding: 0.5rem 1rem;
            border-radius: 8px;
            transition: all 0.3s ease;
            border: none;
            background: none;
            cursor: pointer;
            font-size: 1rem;
        }
        
        .nav-links a:hover, .nav-links button:hover {
            background: #667eea;
            color: white;
        }
        
        .logout-btn {
            background: #ff6b6b !important;
            color: white !important;
        }
        
        .logout-btn:hover {
            background: #ff5252 !important;
        }
        
        .container {
            max-width: 1200px;
            margin: 2rem auto;
            padding: 0 2rem;
        }
        
        .welcome-section {
            background: white;
            border-radius: 20px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            text-align: center;
        }
        
        .welcome-section h1 {
            color: #667eea;
            margin-bottom: 0.5rem;
            font-size: 2.5rem;
        }
        
        .welcome-section p {
            color: #666;
            font-size: 1.1rem;
        }
        
        .tabs {
            display: flex;
            background: white;
            border-radius: 15px;
            padding: 0.5rem;
            margin-bottom: 2rem;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
        }
        
        .tab {
            flex: 1;
            padding: 1rem;
            text-align: center;
            border-radius: 10px;
            cursor: pointer;
            transition: all 0.3s ease;
            border: none;
            background: none;
            font-size: 1rem;
        }
        
        .tab.active {
            background: #667eea;
            color: white;
        }
        
        .tab-content {
            display: none;
            background: white;
            border-radius: 20px;
            padding: 2rem;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        }
        
        .tab-content.active {
            display: block;
        }
        
        .btn {
            background: #667eea;
            color: white;
            padding: 0.75rem 1.5rem;
            border: none;
            border-radius: 10px;
            cursor: pointer;
            font-size: 1rem;
            transition: all 0.3s ease;
            margin: 0.5rem;
        }
        
        .btn:hover {
            background: #5a67d8;
            transform: translateY(-2px);
        }
        
        .btn-danger {
            background: #ff6b6b;
        }
        
        .btn-danger:hover {
            background: #ff5252;
        }
        
        .btn-success {
            background: #51cf66;
        }
        
        .btn-success:hover {
            background: #40c057;
        }
        
        .form-group {
            margin-bottom: 1.5rem;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
            color: #333;
        }
        
        .form-group input,
        .form-group select {
            width: 100%;
            padding: 0.75rem;
            border: 2px solid #e9ecef;
            border-radius: 8px;
            font-size: 1rem;
            transition: border-color 0.3s ease;
        }
        
        .form-group input:focus,
        .form-group select:focus {
            outline: none;
            border-color: #667eea;
        }
        
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 1rem;
        }
        
        .data-table th,
        .data-table td {
            padding: 1rem;
            text-align: left;
            border-bottom: 1px solid #e9ecef;
        }
        
        .data-table th {
            background: #f8f9fa;
            font-weight: 600;
            color: #495057;
        }
        
        .data-table tr:hover {
            background: #f8f9fa;
        }
        
        .child-card {
            border: 1px solid #e9ecef;
            border-radius: 10px;
            padding: 1.5rem;
            margin-bottom: 1rem;
            background: #f8f9fa;
        }
        
        .child-card h4 {
            color: #667eea;
            margin-bottom: 0.5rem;
        }
        
        .grade-display {
            font-weight: bold;
            font-size: 1.1rem;
        }
        
        .grade-a { color: #28a745; }
        .grade-b { color: #17a2b8; }
        .grade-c { color: #ffc107; }
        .grade-d { color: #fd7e14; }
        .grade-f { color: #dc3545; }
        
        .loading {
            text-align: center;
            padding: 2rem;
            color: #666;
        }
        
        .error {
            background: #f8d7da;
            color: #721c24;
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1rem;
        }
        
        .success {
            background: #d1edda;
            color: #155724;
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1rem;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-bottom: 2rem;
        }
        
        .stat-card {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 1.5rem;
            text-align: center;
            border: 1px solid #e9ecef;
        }
        
        .stat-number {
            font-size: 2rem;
            font-weight: bold;
            color: #667eea;
            display: block;
        }
        
        .stat-label {
            color: #666;
            font-size: 0.9rem;
            margin-top: 0.5rem;
        }
        
        @media (max-width: 768px) {
            .nav-container {
                flex-direction: column;
                gap: 1rem;
            }
            
            .nav-links {
                flex-wrap: wrap;
                justify-content: center;
            }
            
            .welcome-section h1 {
                font-size: 2rem;
            }
            
            .container {
                padding: 0 1rem;
            }
            
            .tabs {
                flex-direction: column;
                gap: 0.5rem;
            }
            
            .data-table {
                font-size: 0.875rem;
            }
            
            .data-table th,
            .data-table td {
                padding: 0.5rem;
            }
        }
    </style>
</head>
<body>
    <nav class="navbar">
        <div class="nav-container">
            <div class="logo">EduConnect LMS</div>
            <div class="nav-links">
                <a href="../index.html">Home</a>
                <button onclick="logout()" class="logout-btn">Logout</button>
            </div>
        </div>
    </nav>

    <div class="container">
        <div class="welcome-section">
            <h1>Welcome, <?php echo htmlspecialchars($userName); ?>! 👨‍👩‍👧‍👦</h1>
            <p>Your Parent Dashboard - Monitor your children's academic progress</p>
        </div>

        <div class="tabs">
            <button class="tab active" onclick="showTab('children')">My Children</button>
            <button class="tab" onclick="showTab('grades')">Academic Progress</button>
        </div>

        <!-- Children Tab -->
        <div id="children" class="tab-content active">
            <h2>My Children</h2>
            <div id="childrenLoading" class="loading">Loading children...</div>
            <div id="childrenError" class="error" style="display: none;"></div>
            <div id="childrenList"></div>
            
            <h3 style="margin-top: 2rem;">Link New Child</h3>
            <p style="margin-bottom: 1rem; color: #666;">Enter your child's email address to link their academic progress to your account.</p>
            <div class="form-group">
                <label for="childEmail">Child's Email Address:</label>
                <input type="email" id="childEmail" placeholder="Enter child's student email">
            </div>
            <button onclick="linkChild()" class="btn">Link Child</button>
        </div>

        <!-- Grades Tab -->
        <div id="grades" class="tab-content">
            <h2>Children's Academic Progress</h2>
            <div class="form-group">
                <label for="selectedChild">Select Child:</label>
                <select id="selectedChild" onchange="loadGrades()">
                    <option value="">Select a child to view grades</option>
                </select>
            </div>
            
            <div id="gradesLoading" class="loading" style="display: none;">Loading grades...</div>
            <div id="gradesError" class="error" style="display: none;"></div>
            <div id="gradesList"></div>
            <div id="statisticsSection"></div>
        </div>
    </div>

    <script>
        let currentChildren = [];
        let currentGrades = [];

        // Tab switching functionality
        function showTab(tabName) {
            // Hide all tab contents
            document.querySelectorAll('.tab-content').forEach(content => {
                content.classList.remove('active');
            });
            
            // Remove active class from all tabs
            document.querySelectorAll('.tab').forEach(tab => {
                tab.classList.remove('active');
            });
            
            // Show selected tab content
            document.getElementById(tabName).classList.add('active');
            
            // Add active class to clicked tab
            event.target.classList.add('active');
            
            // Load data based on selected tab
            if (tabName === 'children') {
                loadChildren();
            } else if (tabName === 'grades') {
                loadChildrenOptions();
            }
        }

        // Load children
        async function loadChildren() {
            const loadingEl = document.getElementById('childrenLoading');
            const errorEl = document.getElementById('childrenError');
            const listEl = document.getElementById('childrenList');
            
            loadingEl.style.display = 'block';
            errorEl.style.display = 'none';
            listEl.innerHTML = '';
            
            try {
                const response = await fetch('../api/parent/children.php');
                const data = await response.json();
                
                if (!response.ok) {
                    throw new Error(data.error || 'Failed to load children');
                }
                
                currentChildren = data.children;
                displayChildren(data.children);
                
            } catch (error) {
                errorEl.textContent = error.message;
                errorEl.style.display = 'block';
            } finally {
                loadingEl.style.display = 'none';
            }
        }

        function displayChildren(children) {
            const listEl = document.getElementById('childrenList');
            
            if (children.length === 0) {
                listEl.innerHTML = '<p>No children linked to your account yet.</p>';
                return;
            }
            
            let html = '';
            children.forEach(child => {
                html += `
                    <div class="child-card">
                        <h4>${child.name}</h4>
                        <p><strong>Email:</strong> ${child.email}</p>
                        ${child.created_at ? `<p><strong>Account Created:</strong> ${new Date(child.created_at).toLocaleDateString()}</p>` : ''}
                        <button onclick="unlinkChild('${child.id}')" class="btn btn-danger">Unlink Child</button>
                    </div>
                `;
            });
            
            listEl.innerHTML = html;
        }

        // Load children options for grades dropdown
        async function loadChildrenOptions() {
            const selectEl = document.getElementById('selectedChild');
            
            try {
                const response = await fetch('../api/parent/children.php');
                const data = await response.json();
                
                if (response.ok) {
                    selectEl.innerHTML = '<option value="">Select a child to view grades</option>';
                    data.children.forEach(child => {
                        selectEl.innerHTML += `<option value="${child.id}">${child.name}</option>`;
                    });
                }
            } catch (error) {
                console.error('Failed to load children options:', error);
            }
        }

        // Load grades for selected child
        async function loadGrades() {
            const childId = document.getElementById('selectedChild').value;
            const loadingEl = document.getElementById('gradesLoading');
            const errorEl = document.getElementById('gradesError');
            const listEl = document.getElementById('gradesList');
            const statsEl = document.getElementById('statisticsSection');
            
            if (!childId) {
                listEl.innerHTML = '';
                statsEl.innerHTML = '';
                return;
            }
            
            loadingEl.style.display = 'block';
            errorEl.style.display = 'none';
            listEl.innerHTML = '';
            statsEl.innerHTML = '';
            
            try {
                const response = await fetch(`../api/parent/grades.php?child_id=${childId}`);
                const data = await response.json();
                
                if (!response.ok) {
                    throw new Error(data.error || 'Failed to load grades');
                }
                
                currentGrades = data.grades;
                displayGrades(data.grades, data.statistics);
                
            } catch (error) {
                errorEl.textContent = error.message;
                errorEl.style.display = 'block';
            } finally {
                loadingEl.style.display = 'none';
            }
        }

        function displayGrades(grades, statistics) {
            const listEl = document.getElementById('gradesList');
            const statsEl = document.getElementById('statisticsSection');
            
            if (grades.length === 0) {
                listEl.innerHTML = '<p>No grades available for this child yet.</p>';
                return;
            }
            
            // Display statistics
            let statsHtml = `
                <h3>Academic Performance Summary</h3>
                <div class="stats-grid">
                    <div class="stat-card">
                        <span class="stat-number">${statistics.total_assignments}</span>
                        <div class="stat-label">Total Assignments</div>
                    </div>
                    <div class="stat-card">
                        <span class="stat-number">${statistics.total_points}</span>
                        <div class="stat-label">Points Earned</div>
                    </div>
                    <div class="stat-card">
                        <span class="stat-number">${statistics.max_total_points}</span>
                        <div class="stat-label">Total Possible</div>
                    </div>
                    <div class="stat-card">
                        <span class="stat-number grade-display">${statistics.overall_percentage}%</span>
                        <div class="stat-label">Overall Average</div>
                    </div>
                </div>
            `;
            
            if (Object.keys(statistics.course_averages).length > 0) {
                statsHtml += '<h4>Course Performance</h4>';
                Object.values(statistics.course_averages).forEach(courseData => {
                    const gradeClass = courseData.average >= 90 ? 'grade-a' :
                                      courseData.average >= 80 ? 'grade-b' :
                                      courseData.average >= 70 ? 'grade-c' :
                                      courseData.average >= 60 ? 'grade-d' : 'grade-f';
                    
                    statsHtml += `
                        <div class="child-card">
                            <h4>${courseData.course_title}</h4>
                            <p><strong>Average:</strong> <span class="grade-display ${gradeClass}">${courseData.average}%</span></p>
                            <p><strong>Assignments Completed:</strong> ${courseData.grades.length}</p>
                        </div>
                    `;
                });
            }
            
            statsEl.innerHTML = statsHtml;
            
            // Display grades table
            let html = '<h4>Recent Grades</h4><table class="data-table"><thead><tr><th>Assignment</th><th>Course</th><th>Grade</th><th>Percentage</th><th>Feedback</th><th>Date</th></tr></thead><tbody>';
            
            grades.forEach(grade => {
                const percentage = grade.percentage;
                const gradeClass = percentage >= 90 ? 'grade-a' :
                                  percentage >= 80 ? 'grade-b' :
                                  percentage >= 70 ? 'grade-c' :
                                  percentage >= 60 ? 'grade-d' : 'grade-f';
                
                html += `
                    <tr>
                        <td>${grade.assignment_title}</td>
                        <td>${grade.course_title}</td>
                        <td><span class="grade-display ${gradeClass}">${grade.grade}/${grade.max_points}</span></td>
                        <td><span class="grade-display ${gradeClass}">${percentage}%</span></td>
                        <td>${grade.feedback || 'No feedback'}</td>
                        <td>${grade.graded_at ? new Date(grade.graded_at).toLocaleDateString() : 'Not graded'}</td>
                    </tr>
                `;
            });
            
            html += '</tbody></table>';
            listEl.innerHTML = html;
        }

        // Link child function
        async function linkChild() {
            const email = document.getElementById('childEmail').value.trim();
            
            if (!email) {
                alert('Please enter your child\'s email address');
                return;
            }
            
            try {
                const response = await fetch('../api/parent/children.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({ student_email: email })
                });
                
                const data = await response.json();
                
                if (!response.ok) {
                    throw new Error(data.error || 'Failed to link child');
                }
                
                alert('Child linked successfully!');
                document.getElementById('childEmail').value = '';
                loadChildren();
                
            } catch (error) {
                alert('Error: ' + error.message);
            }
        }

        async function unlinkChild(childId) {
            if (!confirm('Are you sure you want to unlink this child from your account?')) {
                return;
            }
            
            try {
                const response = await fetch('../api/parent/children.php', {
                    method: 'DELETE',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({ student_id: childId })
                });
                
                const data = await response.json();
                
                if (!response.ok) {
                    throw new Error(data.error || 'Failed to unlink child');
                }
                
                alert('Child unlinked successfully!');
                loadChildren();
                
            } catch (error) {
                alert('Error: ' + error.message);
            }
        }

        function logout() {
            if (confirm('Are you sure you want to logout?')) {
                window.location.href = '../api/users/logout.php';
            }
        }

        // Load initial data
        document.addEventListener('DOMContentLoaded', function() {
            loadChildren();
        });
    </script>
</body>
</html>
