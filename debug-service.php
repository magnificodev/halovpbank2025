<?php
require_once 'vendor/autoload.php';
require_once 'src/QRCodeService.php';

use VPBank\QRCodeService;

echo "Testing QRCodeService...\n";

$qrService = new QRCodeService('assets/qr-codes/', '../assets/qr-codes/');

$stationId = 'HALLO_TEST';
$verifyHash = hash('sha256', $stationId . time() . rand());

echo "Station ID: $stationId\n";
echo "Verify Hash: $verifyHash\n";

try {
    $result = $qrService->generateStationQR($stationId, $verifyHash, 'test_service.svg');
    
    echo "Result:\n";
    print_r($result);
    
    echo "File exists: " . (file_exists($result['filepath']) ? 'YES' : 'NO') . "\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
