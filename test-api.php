<?php
// Test API response
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>🧪 Test API Response</h2>";

// Test get-progress API
$token = 'your_token_here'; // Replace with actual token
$url = "http://localhost/halovpbank2025/api/get-progress.php?token=" . urlencode($token);

echo "<h3>Testing: $url</h3>";

$context = stream_context_create([
    'http' => [
        'method' => 'GET',
        'header' => 'Content-Type: application/json'
    ]
]);

$response = file_get_contents($url, false, $context);

echo "<h4>Raw Response:</h4>";
echo "<pre>" . htmlspecialchars($response) . "</pre>";

echo "<h4>JSON Decode Test:</h4>";
$json = json_decode($response, true);
if (json_last_error() === JSON_ERROR_NONE) {
    echo "<p style='color: green;'>✅ Valid JSON</p>";
    echo "<pre>" . print_r($json, true) . "</pre>";
} else {
    echo "<p style='color: red;'>❌ Invalid JSON: " . json_last_error_msg() . "</p>";
}
?>
