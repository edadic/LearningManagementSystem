<?php
session_start();
require_once '../db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'Student') {
    header("Location: ../api/users/login.php");
    exit();
}

$userId = $_SESSION['user_id'];
$db = connectToMongoDB();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Dashboard - EduConnect LMS</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
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
            color: #4facfe;
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
            background: #4facfe;
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
            color: #4facfe;
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
            background: #4facfe;
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
            background: #4facfe;
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
            background: #3d9bfe;
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
        .form-group textarea,
        .form-group select {
            width: 100%;
            padding: 0.75rem;
            border: 2px solid #e9ecef;
            border-radius: 8px;
            font-size: 1rem;
            transition: border-color 0.3s ease;
        }

        .form-group input:focus,
        .form-group textarea:focus,
        .form-group select:focus {
            outline: none;
            border-color: #4facfe;
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

        .status-badge {
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-size: 0.875rem;
            font-weight: 500;
        }

        .status-submitted {
            background: #d1ecf1;
            color: #0c5460;
        }

        .status-pending {
            background: #fff3cd;
            color: #856404;
        }

        .status-graded {
            background: #d4edda;
            color: #155724;
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

        .course-card {
            border: 1px solid #e9ecef;
            border-radius: 10px;
            padding: 1.5rem;
            margin-bottom: 1rem;
            background: #f8f9fa;
        }

        .course-card h4 {
            color: #4facfe;
            margin-bottom: 0.5rem;
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
            max-width: 600px;
            max-height: 80vh;
            overflow-y: auto;
        }

        .close {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
            line-height: 1;
        }

        .close:hover {
            color: #333;
        }

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
            <h1>Welcome, <?php echo htmlspecialchars($_SESSION['name']); ?>! 🎓</h1>
            <p>Your Student Dashboard - Manage your courses, assignments, and grades</p>
        </div>

        <div class="tabs">
            <button class="tab active" onclick="showTab('courses')">My Courses</button>
            <button class="tab" onclick="showTab('assignments')">Assignments</button>
            <button class="tab" onclick="showTab('grades')">Grades</button>
        </div>

        <!-- Courses Tab -->
        <div id="courses" class="tab-content active">
            <h2>My Enrolled Courses</h2>
            <div id="coursesLoading" class="loading">Loading courses...</div>
            <div id="coursesError" class="error" style="display: none;"></div>
            <div id="coursesList">
                <h2>My Enrolled Courses</h2>
                <ul>
                    <?php
                    $enrollments = $db->enrollments->find(['studentId' => $_SESSION['user_id']])->toArray();
                    if (empty($enrollments)) {
                        echo '<p>You are not enrolled in any courses.</p>';
                    } else {
                        foreach ($enrollments as $enrollment):
                            $course = $db->courses->findOne(['_id' => $enrollment['courseId']]);
                            if ($course): ?>
                                <li>
                                    <div class="course-card">
                                        <h4><?php echo htmlspecialchars($course['name']); ?></h4>
                                        <p><strong>Instructor:</strong> <?php echo htmlspecialchars($course['instructor']); ?></p>
                                        <p><strong>Description:</strong> <?php echo htmlspecialchars($course['description']); ?></p>
                                        <button onclick="window.location.href='../views/course_page.php?courseId=<?php echo htmlspecialchars($course['_id']); ?>'" class="btn btn-primary">Go to Course Page</button>
                                    </div>
                                </li>
                            <?php endif;
                        endforeach;
                    }
                    ?>
                </ul>
            </div>
            
            <h3 style="margin-top: 2rem;">Enroll in New Course</h3>
            <p style="margin-bottom: 1rem; color: #666;">Enter either the Course ID or Course Code to enroll in a course.</p>
            
            <div id="availableCoursesSection" style="margin-bottom: 1rem;">
                <h4>Available Courses:</h4>
                <div id="availableCoursesList" style="background: #f8f9fa; padding: 1rem; border-radius: 8px; margin: 0.5rem 0;">
                    Loading available courses...
                </div>
            </div>
            
            <div class="form-group">
                <label for="enrollCourseId">Course ID or Course Code:</label>
                <input type="text" id="enrollCourseId" placeholder="Enter course ID or course code (e.g., CS101, MATH101)">
            </div>
            <button onclick="enrollInCourse()" class="btn">Enroll in Course</button>
        </div>

        <!-- Assignments Tab -->
        <div id="assignments" class="tab-content">
            <h2>My Assignments</h2>
            <div id="assignmentsLoading" class="loading">Loading assignments...</div>
            <div id="assignmentsError" class="error" style="display: none;"></div>
            <div id="assignmentsList"></div>

            
        </div>

        <!-- Grades Tab -->
        <div id="grades" class="tab-content">
            <h2>My Grades</h2>
            <div id="gradesLoading" class="loading">Loading grades...</div>
            <div id="gradesError" class="error" style="display: none;"></div>
            <div id="gradesList"></div>
            <div id="statisticsSection"></div>
        </div>
    </div>

    <!-- Assignment Submission Modal -->
    <div id="submissionModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeSubmissionModal()">&times;</span>
            <h3 id="submissionTitle">Submit Assignment</h3>
            <form id="submissionForm">
                <input type="hidden" id="submissionAssignmentId">
                <div class="form-group">
                    <label for="submissionContent">Assignment Content:</label>
                    <textarea id="submissionContent" rows="10" placeholder="Enter your assignment content here..." required></textarea>
                </div>
                <button type="submit" class="btn">Submit Assignment</button>
                <button type="button" onclick="closeSubmissionModal()" class="btn btn-danger">Cancel</button>
            </form>
        </div>
    </div>

    <script>
        let currentCourses = [];
        let currentAssignments = [];
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
            if (tabName === 'courses') {
                loadCourses();
                loadAvailableCourses();
            } else if (tabName === 'assignments') {
                loadAssignments();
            } else if (tabName === 'grades') {
                loadGrades();
            }
        }

        // Load courses
        async function loadCourses() {
            const loadingEl = document.getElementById('coursesLoading');
            const errorEl = document.getElementById('coursesError');
            const listEl = document.getElementById('coursesList');
            
            loadingEl.style.display = 'block';
            errorEl.style.display = 'none';
            listEl.innerHTML = '';
            
            try {
                const response = await fetch('../api/student/courses.php');
                const data = await response.json();
                
                if (!response.ok) {
                    throw new Error(data.error || 'Failed to load courses');
                }
                
                currentCourses = data.courses;
                displayCourses(data.courses);
                
            } catch (error) {
                errorEl.textContent = error.message;
                errorEl.style.display = 'block';
            } finally {
                loadingEl.style.display = 'none';
            }
        }

        function displayCourses(courses) {
            const listEl = document.getElementById('coursesList');
            
            if (courses.length === 0) {
                listEl.innerHTML = '<p>You are not enrolled in any courses yet.</p>';
                return;
            }
            
            let html = '';
            courses.forEach(course => {
                html += `
                    <div class="course-card">
                        <h4>${course.title}</h4>
                        <p><strong>Instructor:</strong> ${course.instructor}</p>
                        <p><strong>Description:</strong> ${course.description}</p>
                        ${course.created_at ? `<p><strong>Created:</strong> ${new Date(course.created_at).toLocaleDateString()}</p>` : ''}
                        <button onclick="unenrollFromCourse('${course.id}')" class="btn btn-danger">Unenroll</button>
                    </div>
                `;
            });
            
            listEl.innerHTML = html;
        }

        // Load assignments
        async function loadAssignments() {
            const loadingEl = document.getElementById('assignmentsLoading');
            const errorEl = document.getElementById('assignmentsError');
            const listEl = document.getElementById('assignmentsList');
            
            loadingEl.style.display = 'block';
            errorEl.style.display = 'none';
            listEl.innerHTML = '';
            
            try {
                const response = await fetch('../api/student/assignments.php');
                const data = await response.json();
                
                if (!response.ok) {
                    throw new Error(data.error || 'Failed to load assignments');
                }
                
                currentAssignments = data.assignments;
                displayAssignments(data.assignments);
                
            } catch (error) {
                errorEl.textContent = error.message;
                errorEl.style.display = 'block';
            } finally {
                loadingEl.style.display = 'none';
            }
        }

        function displayAssignments(assignments) {
            const listEl = document.getElementById('assignmentsList');
            
            if (assignments.length === 0) {
                listEl.innerHTML = '<p>No assignments found for your enrolled courses.</p>';
                return;
            }
            
            let html = '<table class="data-table"><thead><tr><th>Assignment</th><th>Course</th><th>Due Date</th><th>Status</th><th>Grade</th><th>Actions</th></tr></thead><tbody>';
            
            assignments.forEach(assignment => {
                const dueDate = assignment.due_date ? new Date(assignment.due_date).toLocaleDateString() : 'No due date';
                const status = assignment.submitted ? 
                    (assignment.grade !== null ? 'Graded' : 'Submitted') : 'Pending';
                const statusClass = assignment.submitted ? 
                    (assignment.grade !== null ? 'status-graded' : 'status-submitted') : 'status-pending';
                const grade = assignment.grade !== null ? `${assignment.grade}/${assignment.max_points}` : '-';
                
                html += `
                    <tr>
                        <td>
                            <strong>${assignment.title}</strong><br>
                            <small>${assignment.description}</small>
                        </td>
                        <td>${assignment.course_title}</td>
                        <td>${dueDate}</td>
                        <td><span class="status-badge ${statusClass}">${status}</span></td>
                        <td>${grade}</td>
                        <td>
                            ${!assignment.submitted || assignment.grade === null ? 
                                `<button onclick="openSubmissionModal('${assignment.id}', '${assignment.title}')" class="btn">Submit</button>` : 
                                '<span class="grade-display">Completed</span>'
                            }
                        </td>
                    </tr>
                `;
            });
            
            html += '</tbody></table>';
            listEl.innerHTML = html;
        }

        // Load grades
        async function loadGrades() {
            const loadingEl = document.getElementById('gradesLoading');
            const errorEl = document.getElementById('gradesError');
            const listEl = document.getElementById('gradesList');
            const statsEl = document.getElementById('statisticsSection');
            
            loadingEl.style.display = 'block';
            errorEl.style.display = 'none';
            listEl.innerHTML = '';
            statsEl.innerHTML = '';
            
            try {
                const response = await fetch('../api/student/grades.php');
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
                listEl.innerHTML = '<p>No grades available yet.</p>';
                return;
            }
            
            // Display statistics
            let statsHtml = `
                <h3>Grade Statistics</h3>
                <div class="course-card">
                    <h4>Overall Performance</h4>
                    <p><strong>Total Assignments:</strong> ${statistics.total_assignments}</p>
                    <p><strong>Total Points:</strong> ${statistics.total_points} / ${statistics.max_total_points}</p>
                    <p><strong>Overall Percentage:</strong> <span class="grade-display">${statistics.overall_percentage}%</span></p>
                </div>
            `;
            
            if (Object.keys(statistics.course_averages).length > 0) {
                statsHtml += '<h4>Course Averages</h4>';
                Object.values(statistics.course_averages).forEach(courseData => {
                    statsHtml += `
                        <div class="course-card">
                            <h4>${courseData.course_title}</h4>
                            <p><strong>Average:</strong> <span class="grade-display">${courseData.average}%</span></p>
                            <p><strong>Assignments:</strong> ${courseData.grades.length}</p>
                        </div>
                    `;
                });
            }
            
            statsEl.innerHTML = statsHtml;
            
            // Display grades table
            let html = '<table class="data-table"><thead><tr><th>Assignment</th><th>Course</th><th>Grade</th><th>Percentage</th><th>Feedback</th><th>Graded Date</th></tr></thead><tbody>';
            
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

        // Enrollment functions
        async function enrollInCourse() {
            const courseId = document.getElementById('enrollCourseId').value.trim();
            
            if (!courseId) {
                alert('Please enter a course ID');
                return;
            }
            
            try {
                const response = await fetch('../api/student/courses.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({ course_id: courseId })
                });
                
                const data = await response.json();
                
                if (!response.ok) {
                    throw new Error(data.error || 'Failed to enroll in course');
                }
                
                alert('Successfully enrolled in course!');
                document.getElementById('enrollCourseId').value = '';
                loadCourses();
                
            } catch (error) {
                alert('Error: ' + error.message);
            }
        }

        async function unenrollFromCourse(courseId) {
            if (!confirm('Are you sure you want to unenroll from this course?')) {
                return;
            }
            
            try {
                const response = await fetch('../api/student/courses.php', {
                    method: 'DELETE',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({ course_id: courseId })
                });
                
                const data = await response.json();
                
                if (!response.ok) {
                    throw new Error(data.error || 'Failed to unenroll from course');
                }
                
                alert('Successfully unenrolled from course!');
                loadCourses();
                
            } catch (error) {
                alert('Error: ' + error.message);
            }
        }

        // Assignment submission functions
        function openSubmissionModal(assignmentId, assignmentTitle) {
            document.getElementById('submissionAssignmentId').value = assignmentId;
            document.getElementById('submissionTitle').textContent = `Submit: ${assignmentTitle}`;
            document.getElementById('submissionContent').value = '';
            document.getElementById('submissionModal').style.display = 'block';
        }

        function closeSubmissionModal() {
            document.getElementById('submissionModal').style.display = 'none';
        }

        document.getElementById('submissionForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const assignmentId = document.getElementById('submissionAssignmentId').value;
            const content = document.getElementById('submissionContent').value.trim();
            
            if (!content) {
                alert('Please enter assignment content');
                return;
            }
            
            try {
                const response = await fetch('../api/student/assignments.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        assignment_id: assignmentId,
                        content: content
                    })
                });
                
                const data = await response.json();
                
                if (!response.ok) {
                    throw new Error(data.error || 'Failed to submit assignment');
                }
                
                alert('Assignment submitted successfully!');
                closeSubmissionModal();
                loadAssignments();
                
            } catch (error) {
                alert('Error: ' + error.message);
            }
        });

        function logout() {
            if (confirm('Are you sure you want to logout?')) {
                window.location.href = '../api/users/logout.php';
            }
        }

        // Load initial data
        document.addEventListener('DOMContentLoaded', function() {
            loadCourses();
            loadAvailableCourses();
        });

        // Close modal when clicking outside
        window.onclick = function(event) {
            const modal = document.getElementById('submissionModal');
            if (event.target == modal) {
                closeSubmissionModal();
            }
        }

        // Load available courses for enrollment
        async function loadAvailableCourses() {
            try {
                const response = await fetch('../api/courses/available.php');
                const data = await response.json();
                
                const availableListEl = document.getElementById('availableCoursesList');
                
                if (!response.ok || !Array.isArray(data)) {
                    availableListEl.innerHTML = '<p>Unable to load available courses.</p>';
                    return;
                }
                
                if (data.length === 0) {
                    availableListEl.innerHTML = '<p>No courses available for enrollment.</p>';
                    return;
                }
                
                let html = '<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1rem;">';
                data.forEach(course => {
                    html += `
                        <div style="background: white; padding: 1rem; border-radius: 8px; border-left: 4px solid #4facfe;">
                            <h5 style="color: #4facfe; margin-bottom: 0.5rem;">${course.course_name}</h5>
                            <p><strong>Code:</strong> ${course.course_code}</p>
                            <p><strong>Teacher:</strong> ${course.teacher_name || 'Unknown'}</p>
                            <p style="font-size: 0.9rem; color: #666;">${course.description || 'No description'}</p>
                        </div>
                    `;
                });
                html += '</div>';
                
                availableListEl.innerHTML = html;
                
            } catch (error) {
                const availableListEl = document.getElementById('availableCoursesList');
                availableListEl.innerHTML = '<p>Error loading available courses.</p>';
            }
        }
    </script>
</body>
</html>
