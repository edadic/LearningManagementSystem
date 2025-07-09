# MongoDB Migration Guide for Learning Management System

## Overview
This guide helps you migrate your PHP Learning Management System from MySQL to MongoDB.

## Database Schema Changes

### MySQL vs MongoDB Structure

#### MySQL Tables → MongoDB Collections

| MySQL Table | MongoDB Collection | Key Changes |
|-------------|-------------------|-------------|
| User | users | Same structure, `_id` instead of `id` |
| Teacher | teachers | References `userId` instead of foreign key |
| Student | students | Embedded arrays for courses, grades |
| Parent | parents | Embedded array for children |
| Course | courses | Embedded arrays for students, assignments |
| Assignment | assignments | Embedded submissions array |
| Attendance | attendance | Separate collection with embedded records |
| Grades | grades | Can be embedded in students or separate |

### Key MongoDB Advantages

1. **Flexible Schema**: Easy to add new fields without migrations
2. **Embedded Documents**: Related data stored together for faster queries
3. **Arrays**: Natural support for one-to-many relationships
4. **JSON-like Documents**: Easier to work with in modern applications
5. **Horizontal Scaling**: Better for large datasets

## Migration Steps

### 1. Environment Setup
```bash
# Install MongoDB
brew install mongodb-community

# Install PHP MongoDB extension
pecl install mongodb

# Add to php.ini
echo "extension=mongodb" >> /etc/php.ini

# Install PHP MongoDB library
composer require mongodb/mongodb
```

### 2. Database Connection
- Replace `mysqli` connection with MongoDB client
- Use `MongoDB\Client` instead of `new mysqli()`
- Collections instead of tables

### 3. Query Changes

#### User Registration
**MySQL:**
```sql
INSERT INTO User (name, email, password, role) VALUES (?, ?, ?, ?)
```

**MongoDB:**
```php
$usersCollection->insertOne([
    'name' => $name,
    'email' => $email,
    'password' => $password,
    'role' => $role,
    'created_at' => new MongoDB\BSON\UTCDateTime()
]);
```

#### Finding Users
**MySQL:**
```sql
SELECT * FROM User WHERE email = ?
```

**MongoDB:**
```php
$user = $usersCollection->findOne(['email' => $email]);
```

#### Joins
**MySQL:**
```sql
SELECT c.*, u.name as teacher_name 
FROM Course c 
JOIN User u ON c.teacherId = u.id
```

**MongoDB:**
```php
$courses = $coursesCollection->aggregate([
    [
        '$lookup' => [
            'from' => 'users',
            'localField' => 'teacherId',
            'foreignField' => '_id',
            'as' => 'teacher'
        ]
    ]
]);
```

### 4. File Updates Required

1. **Database Connection**: `backend/mongodb.php` (new)
2. **User Management**: 
   - `backend/api/users/register_mongodb.php`
   - `backend/api/users/login.php`
3. **Course Management**:
   - `backend/api/courses/add_course_mongodb.php`
   - `backend/api/courses/view_course_mongodb.php`
4. **Assignment Management**:
   - `backend/api/assignments/add_assignment_mongodb.php`
   - `backend/api/assignments/submit_assignment_mongodb.php`
5. **Dashboards**: Updated with MongoDB file references

### 5. Data Migration Script

If you have existing MySQL data, create a migration script:

```php
<?php
// migration_script.php
include 'backend/db.php'; // MySQL connection
include 'backend/mongodb.php'; // MongoDB connection

// Migrate users
$result = $conn->query("SELECT * FROM User");
while ($row = $result->fetch_assoc()) {
    $usersCollection->insertOne([
        'name' => $row['name'],
        'email' => $row['email'],
        'password' => $row['password'],
        'role' => $row['role'],
        'created_at' => new MongoDB\BSON\UTCDateTime(),
        'legacy_id' => $row['id'] // Keep for reference
    ]);
}
```

## Running the MongoDB Version

1. **Start MongoDB**:
   ```bash
   brew services start mongodb/brew/mongodb-community
   ```

2. **Initialize Database**:
   ```bash
   php backend/init_mongodb.php
   ```

3. **Test Connection**:
   - Visit: `http://localhost/your-project/backend/api/users/register_mongodb.php`
   - Register a new user
   - Login and test functionality

4. **Access Dashboards**:
   - Teacher: `frontend/views/teacher/teacher_dashboard_mongodb.php`
   - Student: `frontend/views/student/student_dashboard.php`
   - Parent: `frontend/views/parent/parent_dashboard.php`

## Benefits of MongoDB Migration

1. **Scalability**: Horizontal scaling capabilities
2. **Flexibility**: Schema-less design for rapid development
3. **Performance**: Faster queries for embedded documents
4. **Modern**: Better suited for modern web applications
5. **JSON Native**: Easier API development

## Troubleshooting

### Common Issues:
1. **Extension not loaded**: Check `php -m | grep mongodb`
2. **Connection failed**: Ensure MongoDB service is running
3. **Permission errors**: Check MongoDB data directory permissions
4. **Composer errors**: Install mongodb/mongodb package

### Debugging:
```php
// Test MongoDB connection
try {
    $client = new MongoDB\Client("mongodb://localhost:27017");
    $databases = $client->listDatabases();
    echo "MongoDB connected successfully!";
} catch (Exception $e) {
    echo "Connection failed: " . $e->getMessage();
}
```
