<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "<h1>PHP Debug Mode</h1>";
echo "PHP Version: " . phpversion() . "<br>";
echo "Current Directory: " . __DIR__ . "<br>";

if (extension_loaded('mysqli')) {
    echo "✅ mysqli extension is LOADED<br>";
} else {
    echo "❌ mysqli extension is MISSING<br>";
}

$db_path = __DIR__ . '/../backend/includes/database.php';
echo "Checking database file at: " . $db_path . "<br>";
if (file_exists($db_path)) {
    echo "✅ database.php EXISTS<br>";
    if (is_readable($db_path)) {
        echo "✅ database.php is READABLE<br>";
        require $db_path;
        if (isset($db) && $db) {
            echo "✅ Database CONNECTION SUCCESSFUL<br>";
        } else {
            echo "❌ Database CONNECTION FAILED<br>";
        }
    } else {
        echo "❌ database.php is NOT READABLE<br>";
    }
} else {
    echo "❌ database.php NOT FOUND<br>";
}

echo "<h2>Session Test</h2>";
if (session_start()) {
    echo "✅ session_start() SUCCESSFUL<br>";
} else {
    echo "❌ session_start() FAILED<br>";
}
?>
