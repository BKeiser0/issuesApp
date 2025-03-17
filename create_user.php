<?php
// Database connection
$host = 'localhost';
$dbname = 'finalproject';
$username = 'root';  // Default XAMPP MySQL user
$password = '';      // Default XAMPP MySQL password (empty)

// Connect to MySQL
try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

// Function to create a new user
function createUser($fname, $lname, $mobile, $email, $plain_password, $is_admin)
{
    global $pdo;

    // Generate a random salt
    $salt = bin2hex(random_bytes(16));  // 32-character salt

    // Hash the password using MD5 and the salt
    $hashed_password = md5($plain_password . $salt);

    // Insert user into database
    $sql = "INSERT INTO iss_persons (fname, lname, mobile, email, pwd_hash, pwd_salt, admin) 
            VALUES (:fname, :lname, :mobile, :email, :pwd_hash, :pwd_salt, :admin)";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':fname'    => $fname,
        ':lname'    => $lname,
        ':mobile'   => $mobile,
        ':email'    => $email,
        ':pwd_hash' => $hashed_password,
        ':pwd_salt' => $salt,
        ':admin'    => $is_admin
    ]);

    echo "User successfully created!";
}

// Test user creation (Change the details as needed)
createUser('Brady', 'Keiser', '231-231-2310', 'bjkeiser@svsu.edu', 'SecurePass123', 'no');

?>
