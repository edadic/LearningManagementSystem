# MongoDB Setup Guide for LMS

## 1. Install MongoDB

### On macOS (using Homebrew):
```bash
# Install MongoDB
brew tap mongodb/brew
brew install mongodb-community

# Start MongoDB service
brew services start mongodb/brew/mongodb-community
```

### Alternative using Docker:
```bash
# Run MongoDB in Docker container
docker run -d --name mongodb -p 27017:27017 -e MONGO_INITDB_ROOT_USERNAME=admin -e MONGO_INITDB_ROOT_PASSWORD=password mongo:latest
```

## 2. Install PHP MongoDB Extension

### On macOS:
```bash
# Install via pecl
pecl install mongodb

# Or if using Homebrew PHP
brew install php-mongodb
```

### Add to php.ini:
Add this line to your php.ini file:
```
extension=mongodb
```

To find your php.ini location, run:
```bash
php --ini
```

## 3. Install MongoDB PHP Library

After installing the extension, run:
```bash
composer require mongodb/mongodb
```

## 4. Verify Installation

Create a test file to verify MongoDB connection:
```php
<?php
require_once 'vendor/autoload.php';

try {
    $client = new MongoDB\Client("mongodb://localhost:27017");
    $db = $client->selectDatabase('test');
    echo "MongoDB connection successful!";
} catch (Exception $e) {
    echo "Connection failed: " . $e->getMessage();
}
?>
```
