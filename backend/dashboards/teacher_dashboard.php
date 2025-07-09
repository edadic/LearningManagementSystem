<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'Teacher') {
    header("Location: ../api/users/login.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Teacher Dashboard - EduConnect LMS</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #a8edea 0%, #fed6e3 100%);
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
            color: #20b2aa;
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
            background: #20b2aa;
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
            color: #20b2aa;
            margin-bottom: 0.5rem;
            font-size: 2.5rem;
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
            background: #20b2aa;
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
            background: #20b2aa;
            color: white;
            padding: 0.75rem 1.5rem;
            border: none;
            border-radius: 10px;
            cursor: pointer;
            font-size: 1rem;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-block;
        }

        .btn:hover {
            background: #1a9b94;
            transform: translateY(-2px);
        }

        .btn-secondary {
            background: #6c757d;
        }

        .btn-secondary:hover {
            background: #5a6268;
        }

        .btn-danger {
            background: #dc3545;
        }

        .btn-danger:hover {
            background: #c82333;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
        }

        .form-group input, .form-group textarea, .form-group select {
            width: 100%;
            padding: 0.75rem;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 1rem;
            transition: border-color 0.3s ease;
        }

        .form-group input:focus, .form-group textarea:focus, .form-group select:focus {
            outline: none;
            border-color: #20b2aa;
        }

        .grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 1.5rem;
            margin-top: 2rem;
        }

        .card {
            background: #f8f9fa;
            border-radius: 15px;
            padding: 1.5rem;
            border-left: 4px solid #20b2aa;
            transition: all 0.3s ease;
        }

        .card:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
        }

        .card h3 {
            color: #20b2aa;
            margin-bottom: 1rem;
        }

        .card-actions {
            margin-top: 1rem;
            display: flex;
            gap: 0.5rem;
        }

        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
        }

        .modal-content {
            background-color: white;
            margin: 5% auto;
            padding: 2rem;
            border-radius: 15px;
            width: 90%;
            max-width: 500px;
        }

        .close {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
        }

        .close:hover {
            color: black;
        }

        .error {
            color: #dc3545;
            background: #f8d7da;
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1rem;
        }

        .success {
            color: #155724;
            background: #d4edda;
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1rem;
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

            .tabs {
                flex-direction: column;
            }

            .container {
                padding: 0 1rem;
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
                <button onclick="showTab('courses')">Courses</button>
                <button onclick="showTab('assignments')">Assignments</button>
                <button onclick="showTab('grades')">Grades</button>
                <a href="../api/users/logout.php" class="logout-btn">Logout</a>
            </div>
        </div>
    </nav>

    <div class="container">
        <div class="welcome-section">
            <h1>Welcome back, <?php echo htmlspecialchars($_SESSION['name']); ?>! 👨‍🏫</h1>
            <p>Manage your courses, assignments, and student grades</p>
        </div>

        <div class="tabs">
            <button class="tab active" onclick="showTab('courses')">My Courses</button>
            <button class="tab" onclick="showTab('assignments')">Assignments</button>
            <button class="tab" onclick="showTab('grades')">Grade Management</button>
        </div>

        <!-- Courses Tab -->
        <div id="courses" class="tab-content active">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
                <h2>My Courses</h2>
                <button class="btn" onclick="showModal('courseModal')">Add New Course</button>
            </div>
            <div id="coursesList" class="grid">
                <!-- Courses will be loaded here -->
            </div>
        </div>

        <!-- Assignments Tab -->
        <div id="assignments" class="tab-content">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
                <h2>Assignments</h2>
                <button class="btn" onclick="showModal('assignmentModal')">Create Assignment</button>
            </div>
            <div id="assignmentsList" class="grid">
                <!-- Assignments will be loaded here -->
            </div>
        </div>

        <!-- Grades Tab -->
        <div id="grades" class="tab-content">
            <h2>Grade Management</h2>
            <p>Select an assignment to grade student submissions:</p>
            <div id="gradesList">
                <!-- Grading interface will be loaded here -->
            </div>
        </div>
    </div>

    <!-- Course Modal -->
    <div id="courseModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="hideModal('courseModal')">&times;</span>
            <h2>Add New Course</h2>
            <form id="courseForm">
                <div class="form-group">
                    <label for="courseName">Course Name</label>
                    <input type="text" id="courseName" name="course_name" required>
                </div>
                <div class="form-group">
                    <label for="courseCode">Course Code</label>
                    <input type="text" id="courseCode" name="course_code" required>
                </div>
                <div class="form-group">
                    <label for="courseDescription">Description</label>
                    <textarea id="courseDescription" name="description" rows="3"></textarea>
                </div>
                <button type="submit" class="btn">Create Course</button>
                <button type="button" class="btn btn-secondary" onclick="hideModal('courseModal')">Cancel</button>
            </form>
        </div>
    </div>

    <!-- Assignment Modal -->
    <div id="assignmentModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="hideModal('assignmentModal')">&times;</span>
            <h2>Create Assignment</h2>
            <form id="assignmentForm">
                <div class="form-group">
                    <label for="assignmentTitle">Assignment Title</label>
                    <input type="text" id="assignmentTitle" name="title" required>
                </div>
                <div class="form-group">
                    <label for="assignmentCourse">Course</label>
                    <select id="assignmentCourse" name="course_id" required>
                        <option value="">Select a course</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="assignmentDescription">Description</label>
                    <textarea id="assignmentDescription" name="description" rows="3"></textarea>
                </div>
                <div class="form-group">
                    <label for="assignmentDueDate">Due Date</label>
                    <input type="datetime-local" id="assignmentDueDate" name="due_date">
                </div>
                <div class="form-group">
                    <label for="assignmentPoints">Max Points</label>
                    <input type="number" id="assignmentPoints" name="max_points" value="100" min="1">
                </div>
                <button type="submit" class="btn">Create Assignment</button>
                <button type="button" class="btn btn-secondary" onclick="hideModal('assignmentModal')">Cancel</button>
            </form>
        </div>
    </div>

    <script>
        // Tab switching
        function showTab(tabName) {
            // Hide all tab contents
            document.querySelectorAll('.tab-content').forEach(tab => {
                tab.classList.remove('active');
            });
            
            // Remove active class from all tabs
            document.querySelectorAll('.tab').forEach(tab => {
                tab.classList.remove('active');
            });
            
            // Show selected tab
            document.getElementById(tabName).classList.add('active');
            
            // Add active class to clicked tab
            event.target.classList.add('active');
            
            // Load data for the selected tab
            switch(tabName) {
                case 'courses':
                    loadCourses();
                    break;
                case 'assignments':
                    loadAssignments();
                    break;
                case 'grades':
                    loadGrades();
                    break;
            }
        }

        // Modal functions
        function showModal(modalId) {
            document.getElementById(modalId).style.display = 'block';
            if (modalId === 'assignmentModal') {
                loadCoursesForSelect();
            }
        }

        function hideModal(modalId) {
            document.getElementById(modalId).style.display = 'none';
        }

        // Load courses
        async function loadCourses() {
            try {
                const response = await fetch('../api/teacher/courses.php');
                const courses = await response.json();
                
                const coursesList = document.getElementById('coursesList');
                coursesList.innerHTML = '';
                
                courses.forEach(course => {
                    const courseCard = document.createElement('div');
                    courseCard.className = 'card';
                    courseCard.innerHTML = `
                        <h3>${course.course_name}</h3>
                        <p><strong>Code:</strong> ${course.course_code}</p>
                        <p>${course.description || 'No description'}</p>
                        <div class="card-actions">
                            <button class="btn btn-secondary" onclick="editCourse('${course._id}')">Edit</button>
                            <button class="btn btn-danger" onclick="deleteCourse('${course._id}')">Delete</button>
                        </div>
                    `;
                    coursesList.appendChild(courseCard);
                });
                
                if (courses.length === 0) {
                    coursesList.innerHTML = '<p>No courses found. Create your first course!</p>';
                }
            } catch (error) {
                console.error('Error loading courses:', error);
                showError('Failed to load courses');
            }
        }

        // Load assignments
        async function loadAssignments() {
            try {
                const response = await fetch('../api/teacher/assignments.php');
                const assignments = await response.json();
                
                const assignmentsList = document.getElementById('assignmentsList');
                assignmentsList.innerHTML = '';
                
                assignments.forEach(assignment => {
                    const dueDate = assignment.due_date ? new Date(assignment.due_date).toLocaleDateString() : 'No due date';
                    const assignmentCard = document.createElement('div');
                    assignmentCard.className = 'card';
                    assignmentCard.innerHTML = `
                        <h3>${assignment.title}</h3>
                        <p><strong>Course:</strong> ${assignment.course_name}</p>
                        <p><strong>Due:</strong> ${dueDate}</p>
                        <p><strong>Points:</strong> ${assignment.max_points}</p>
                        <p>${assignment.description || 'No description'}</p>
                        <div class="card-actions">
                            <button class="btn btn-secondary" onclick="editAssignment('${assignment._id}')">Edit</button>
                            <button class="btn btn-danger" onclick="deleteAssignment('${assignment._id}')">Delete</button>
                        </div>
                    `;
                    assignmentsList.appendChild(assignmentCard);
                });
                
                if (assignments.length === 0) {
                    assignmentsList.innerHTML = '<p>No assignments found. Create your first assignment!</p>';
                }
            } catch (error) {
                console.error('Error loading assignments:', error);
                showError('Failed to load assignments');
            }
        }

        // Load courses for assignment select
        async function loadCoursesForSelect() {
            try {
                const response = await fetch('../api/teacher/courses.php');
                const courses = await response.json();
                
                const select = document.getElementById('assignmentCourse');
                select.innerHTML = '<option value="">Select a course</option>';
                
                courses.forEach(course => {
                    const option = document.createElement('option');
                    option.value = course._id;
                    option.textContent = `${course.course_name} (${course.course_code})`;
                    select.appendChild(option);
                });
            } catch (error) {
                console.error('Error loading courses for select:', error);
            }
        }

        // Load grades
        async function loadGrades() {
            document.getElementById('gradesList').innerHTML = '<p>Grade management coming soon!</p>';
        }

        // Form submissions
        document.getElementById('courseForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            
            const formData = new FormData(e.target);
            const courseData = Object.fromEntries(formData.entries());
            
            try {
                const response = await fetch('../api/teacher/courses.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify(courseData)
                });
                
                const result = await response.json();
                
                if (result.success) {
                    hideModal('courseModal');
                    e.target.reset();
                    loadCourses();
                    showSuccess('Course created successfully!');
                } else {
                    showError(result.error || 'Failed to create course');
                }
            } catch (error) {
                console.error('Error creating course:', error);
                showError('Failed to create course');
            }
        });

        document.getElementById('assignmentForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            
            const formData = new FormData(e.target);
            const assignmentData = Object.fromEntries(formData.entries());
            
            try {
                const response = await fetch('../api/teacher/assignments.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify(assignmentData)
                });
                
                const result = await response.json();
                
                if (result.success) {
                    hideModal('assignmentModal');
                    e.target.reset();
                    loadAssignments();
                    showSuccess('Assignment created successfully!');
                } else {
                    showError(result.error || 'Failed to create assignment');
                }
            } catch (error) {
                console.error('Error creating assignment:', error);
                showError('Failed to create assignment');
            }
        });

        // Delete functions
        async function deleteCourse(courseId) {
            if (!confirm('Are you sure you want to delete this course?')) return;
            
            try {
                const response = await fetch('../api/teacher/courses.php', {
                    method: 'DELETE',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({ course_id: courseId })
                });
                
                const result = await response.json();
                
                if (result.success) {
                    loadCourses();
                    showSuccess('Course deleted successfully!');
                } else {
                    showError('Failed to delete course');
                }
            } catch (error) {
                console.error('Error deleting course:', error);
                showError('Failed to delete course');
            }
        }

        async function deleteAssignment(assignmentId) {
            if (!confirm('Are you sure you want to delete this assignment?')) return;
            
            try {
                const response = await fetch('../api/teacher/assignments.php', {
                    method: 'DELETE',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({ assignment_id: assignmentId })
                });
                
                const result = await response.json();
                
                if (result.success) {
                    loadAssignments();
                    showSuccess('Assignment deleted successfully!');
                } else {
                    showError('Failed to delete assignment');
                }
            } catch (error) {
                console.error('Error deleting assignment:', error);
                showError('Failed to delete assignment');
            }
        }

        // Utility functions
        function showError(message) {
            // Create and show error message
            const errorDiv = document.createElement('div');
            errorDiv.className = 'error';
            errorDiv.textContent = message;
            
            const container = document.querySelector('.container');
            container.insertBefore(errorDiv, container.firstChild);
            
            setTimeout(() => {
                errorDiv.remove();
            }, 5000);
        }

        function showSuccess(message) {
            // Create and show success message
            const successDiv = document.createElement('div');
            successDiv.className = 'success';
            successDiv.textContent = message;
            
            const container = document.querySelector('.container');
            container.insertBefore(successDiv, container.firstChild);
            
            setTimeout(() => {
                successDiv.remove();
            }, 5000);
        }

        // Edit functions (placeholder)
        function editCourse(courseId) {
            alert('Edit course functionality coming soon!');
        }

        function editAssignment(assignmentId) {
            alert('Edit assignment functionality coming soon!');
        }

        // Load initial data
        document.addEventListener('DOMContentLoaded', () => {
            loadCourses();
        });

        // Close modal when clicking outside
        window.onclick = function(event) {
            const modals = document.querySelectorAll('.modal');
            modals.forEach(modal => {
                if (event.target === modal) {
                    modal.style.display = 'none';
                }
            });
        }
    </script>
</body>
</html>
