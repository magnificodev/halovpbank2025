<?php

namespace VPBank;

use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;
use Endroid\QrCode\Writer\SvgWriter;
use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\RoundBlockSizeMode;
use Endroid\QrCode\Encoding\Encoding;

class QRCodeService {
    private $qrDirectory;
    private $baseUrl;

    public function __construct($qrDirectory = 'assets/qr-codes/', $baseUrl = '') {
        $this->qrDirectory = $qrDirectory;
        $this->baseUrl = $baseUrl;
<<<<<<< HEAD
        
=======

>>>>>>> 474d4931da60f101bdeca6c45815177aff91c0d9
        // Create directory if it doesn't exist
        if (!is_dir($this->qrDirectory)) {
            mkdir($this->qrDirectory, 0755, true);
        }
    }

    /**
     * Generate QR code and save to file
     */
    public function generateQRCode($data, $filename = null, $format = 'png', $size = 300) {
        if (!$filename) {
            $filename = 'qr_' . uniqid() . '.' . $format;
        }

        $qrCode = QrCode::create($data)
            ->setSize($size)
            ->setMargin(10)
            ->setErrorCorrectionLevel(ErrorCorrectionLevel::HIGH)
            ->setRoundBlockSizeMode(RoundBlockSizeMode::MARGIN);

        // Choose writer based on format
        if ($format === 'svg') {
            $writer = new SvgWriter();
        } else {
            $writer = new PngWriter();
        }

        $result = $writer->write($qrCode);
        $filePath = $this->qrDirectory . $filename;
<<<<<<< HEAD
        
        // Save to file
        $result->saveToFile($filePath);
        
=======

        // Save to file
        $result->saveToFile($filePath);

>>>>>>> 474d4931da60f101bdeca6c45815177aff91c0d9
        return [
            'filename' => $filename,
            'filepath' => $filePath,
            'url' => $this->baseUrl . $filePath,
            'size' => filesize($filePath),
            'format' => $format
        ];
    }

    /**
     * Generate QR code and output directly to browser
     */
    public function outputQRCode($data, $format = 'png', $size = 300) {
        $qrCode = QrCode::create($data)
            ->setSize($size)
            ->setMargin(10)
            ->setErrorCorrectionLevel(ErrorCorrectionLevel::HIGH)
            ->setRoundBlockSizeMode(RoundBlockSizeMode::MARGIN);

        if ($format === 'svg') {
            $writer = new SvgWriter();
        } else {
            $writer = new PngWriter();
        }

        $result = $writer->write($qrCode);
<<<<<<< HEAD
        
=======

>>>>>>> 474d4931da60f101bdeca6c45815177aff91c0d9
        header('Content-Type: ' . $result->getMimeType());
        echo $result->getString();
        exit;
    }

    /**
     * Generate QR code for station
     */
    public function generateStationQR($stationId, $verifyHash, $filename = null) {
        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'];
        $baseUrl = $protocol . '://' . $host . dirname($_SERVER['REQUEST_URI'], 2);
        $qrUrl = $baseUrl . '/game.php?station=' . urlencode($stationId) . '&verify=' . $verifyHash;
<<<<<<< HEAD
        
        if (!$filename) {
            $filename = 'station_' . $stationId . '_' . substr($verifyHash, 0, 8) . '.png';
        }
        
=======

        if (!$filename) {
            $filename = 'station_' . $stationId . '_' . substr($verifyHash, 0, 8) . '.png';
        }

>>>>>>> 474d4931da60f101bdeca6c45815177aff91c0d9
        return $this->generateQRCode($qrUrl, $filename, 'png', 400);
    }

    /**
     * Get QR code info
     */
    public function getQRCodeInfo($filename) {
        $filePath = $this->qrDirectory . $filename;
<<<<<<< HEAD
        
        if (!file_exists($filePath)) {
            return null;
        }
        
=======

        if (!file_exists($filePath)) {
            return null;
        }

>>>>>>> 474d4931da60f101bdeca6c45815177aff91c0d9
        return [
            'filename' => $filename,
            'filepath' => $filePath,
            'url' => $this->baseUrl . $filePath,
            'size' => filesize($filePath),
            'created' => filemtime($filePath),
            'format' => pathinfo($filename, PATHINFO_EXTENSION)
        ];
    }

    /**
     * Delete QR code file
     */
    public function deleteQRCode($filename) {
        $filePath = $this->qrDirectory . $filename;
<<<<<<< HEAD
        
        if (file_exists($filePath)) {
            return unlink($filePath);
        }
        
=======

        if (file_exists($filePath)) {
            return unlink($filePath);
        }

>>>>>>> 474d4931da60f101bdeca6c45815177aff91c0d9
        return false;
    }

    /**
     * List all QR codes
     */
    public function listQRCodes() {
        $files = glob($this->qrDirectory . '*');
        $qrCodes = [];
<<<<<<< HEAD
        
=======

>>>>>>> 474d4931da60f101bdeca6c45815177aff91c0d9
        foreach ($files as $file) {
            if (is_file($file)) {
                $filename = basename($file);
                $qrCodes[] = $this->getQRCodeInfo($filename);
            }
        }
<<<<<<< HEAD
        
=======

>>>>>>> 474d4931da60f101bdeca6c45815177aff91c0d9
        // Sort by creation time (newest first)
        usort($qrCodes, function($a, $b) {
            return $b['created'] - $a['created'];
        });
<<<<<<< HEAD
        
=======

>>>>>>> 474d4931da60f101bdeca6c45815177aff91c0d9
        return $qrCodes;
    }
}
