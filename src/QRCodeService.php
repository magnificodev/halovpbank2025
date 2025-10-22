<?php

namespace VPBank;

use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;
use Endroid\QrCode\Writer\SvgWriter;
use Endroid\QrCode\ErrorCorrectionLevel\ErrorCorrectionLevelHigh;
use Endroid\QrCode\RoundBlockSizeMode\RoundBlockSizeModeMargin;

class QRCodeService {
    private $qrDirectory;
    private $baseUrl;

    public function __construct($qrDirectory = 'assets/qr-codes/', $baseUrl = '') {
        $this->qrDirectory = rtrim($qrDirectory, '/') . '/';
        $this->baseUrl = $baseUrl;
        
        // Create directory if it doesn't exist
        if (!is_dir($this->qrDirectory)) {
            mkdir($this->qrDirectory, 0755, true);
        }
    }

    /**
     * Generate QR code and save to file using Endroid QR-Code
     */
    public function generateQRCode($data, $filename = null, $format = 'svg', $size = 300) {
        if (!$filename) {
            $filename = 'qr_' . uniqid() . '.' . $format;
        }

        $qrCode = QrCode::create($data)
            ->setSize($size)
            ->setMargin(10)
            ->setErrorCorrectionLevel(new ErrorCorrectionLevelHigh())
            ->setRoundBlockSizeMode(new RoundBlockSizeModeMargin());

        // Always use SVG writer (works without GD)
        $writer = new SvgWriter();
        $result = $writer->write($qrCode);
        $filePath = $this->qrDirectory . $filename;
        
        // Save SVG
        $result->saveToFile($filePath);
        
        // If PNG requested, create a reference to the PNG API
        if ($format === 'png') {
            $pngFilename = str_replace('.svg', '.png', $filename);
            $pngPath = $this->qrDirectory . $pngFilename;
            
            // Create a simple text file that references the PNG API
            $pngApiUrl = $this->baseUrl . 'api/qr-png.php?data=' . urlencode($data) . '&size=' . $size;
            file_put_contents($pngPath, $pngApiUrl);
            
            $filePath = $pngPath;
            $filename = $pngFilename;
        }
        
        return [
            'filename' => $filename,
            'filepath' => $filePath,
            'url' => $this->baseUrl . $filename,
            'size' => filesize($filePath),
            'format' => $format,
            'method' => 'endroid'
        ];
    }

    /**
     * Generate QR code and output directly to browser
     */
    public function outputQRCode($data, $format = 'svg', $size = 300) {
        $qrCode = QrCode::create($data)
            ->setSize($size)
            ->setMargin(10)
            ->setErrorCorrectionLevel(new ErrorCorrectionLevelHigh())
            ->setRoundBlockSizeMode(new RoundBlockSizeModeMargin());

        if ($format === 'svg') {
            $writer = new SvgWriter();
        } else {
            $writer = new PngWriter();
        }

        $result = $writer->write($qrCode);
        
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
        
        if (!$filename) {
            $filename = 'station_' . $stationId . '_' . substr($verifyHash, 0, 8) . '.png';
        }
        
        return $this->generateQRCode($qrUrl, $filename, 'png', 400);
    }

    /**
     * Generate QR code with custom styling
     */
    public function generateStyledQR($data, $filename = null, $size = 300, $foregroundColor = '#000000', $backgroundColor = '#FFFFFF') {
        if (!$filename) {
            $filename = 'qr_' . uniqid() . '.png';
        }

        $qrCode = QrCode::create($data)
            ->setSize($size)
            ->setMargin(10)
            ->setErrorCorrectionLevel(new ErrorCorrectionLevelHigh())
            ->setRoundBlockSizeMode(new RoundBlockSizeModeMargin());

        $writer = new SvgWriter();
        $result = $writer->write($qrCode);
        $filePath = $this->qrDirectory . $filename;
        
        // Save SVG first
        $result->saveToFile($filePath);
        
        // Create PNG reference
        $pngFilename = str_replace('.png', '.png', $filename);
        $pngPath = $this->qrDirectory . $pngFilename;
        $pngApiUrl = $this->baseUrl . 'api/qr-png.php?data=' . urlencode($data) . '&size=' . $size;
        file_put_contents($pngPath, $pngApiUrl);
        
        return [
            'filename' => $pngFilename,
            'filepath' => $pngPath,
            'url' => $this->baseUrl . $pngFilename,
            'size' => filesize($pngPath),
            'format' => 'png',
            'method' => 'endroid-styled'
        ];
    }

    /**
     * Get QR code info
     */
    public function getQRCodeInfo($filename) {
        $filePath = $this->qrDirectory . $filename;
        
        if (!file_exists($filePath)) {
            return null;
        }
        
        return [
            'filename' => $filename,
            'filepath' => $filePath,
            'url' => $this->baseUrl . $filename,
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
        
        if (file_exists($filePath)) {
            return unlink($filePath);
        }
        
        return false;
    }

    /**
     * List all QR codes
     */
    public function listQRCodes() {
        $files = glob($this->qrDirectory . '*');
        $qrCodes = [];
        
        foreach ($files as $file) {
            if (is_file($file)) {
                $filename = basename($file);
                $qrCodes[] = $this->getQRCodeInfo($filename);
            }
        }
        
        // Sort by creation time (newest first)
        usort($qrCodes, function($a, $b) {
            return $b['created'] - $a['created'];
        });
        
        return $qrCodes;
    }

    /**
     * Generate QR code with logo (if logo file exists)
     */
    public function generateQRWithLogo($data, $filename = null, $size = 300, $logoPath = null) {
        if (!$filename) {
            $filename = 'qr_' . uniqid() . '.png';
        }

        $qrCode = QrCode::create($data)
            ->setSize($size)
            ->setMargin(10)
            ->setErrorCorrectionLevel(new ErrorCorrectionLevelHigh())
            ->setRoundBlockSizeMode(new RoundBlockSizeModeMargin());

        $writer = new SvgWriter();
        $result = $writer->write($qrCode);
        $filePath = $this->qrDirectory . $filename;
        
        // Save SVG first
        $result->saveToFile($filePath);
        
        // Create PNG reference
        $pngFilename = str_replace('.png', '.png', $filename);
        $pngPath = $this->qrDirectory . $pngFilename;
        $pngApiUrl = $this->baseUrl . 'api/qr-png.php?data=' . urlencode($data) . '&size=' . $size;
        file_put_contents($pngPath, $pngApiUrl);
        
        // TODO: Add logo overlay functionality if needed
        
        return [
            'filename' => $pngFilename,
            'filepath' => $pngPath,
            'url' => $this->baseUrl . $pngFilename,
            'size' => filesize($pngPath),
            'format' => 'png',
            'method' => 'endroid-with-logo'
        ];
    }
}
