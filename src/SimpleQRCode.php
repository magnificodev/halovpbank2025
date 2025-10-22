<?php

namespace VPBank;

class SimpleQRCode {

    /**
     * Generate QR code using Google Charts API (fallback)
     */
    public static function generateWithGoogle($data, $size = 300) {
        $url = 'https://chart.googleapis.com/chart?chs=' . $size . 'x' . $size . '&chld=L|0&cht=qr&chl=' . urlencode($data);
        return $url;
    }

    /**
     * Generate QR code using local QR library
     */
    public static function generate($data, $size = 300, $margin = 10) {
        // Simple QR code generation using basic algorithm
        $qr = self::createQRMatrix($data);
        return self::renderQRCode($qr, $size, $margin);
    }

    /**
     * Create QR code matrix (simplified version)
     */
    private static function createQRMatrix($data) {
        // This is a simplified QR code generation
        // For production, you should use a proper QR library

        $matrix = [];
        $size = 21; // Minimum QR code size

        // Create a simple pattern (this is just for demonstration)
        for ($y = 0; $y < $size; $y++) {
            $matrix[$y] = [];
            for ($x = 0; $x < $size; $x++) {
                // Simple pattern based on data hash
                $hash = md5($data . $x . $y);
                $matrix[$y][$x] = (hexdec($hash[0]) % 2) == 0;
            }
        }

        return $matrix;
    }

    /**
     * Render QR code as image data
     */
    private static function renderQRCode($matrix, $size, $margin) {
        $cellSize = ($size - 2 * $margin) / count($matrix);

        // Create image
        $image = imagecreate($size, $size);
        $white = imagecolorallocate($image, 255, 255, 255);
        $black = imagecolorallocate($image, 0, 0, 0);

        // Fill background
        imagefill($image, 0, 0, $white);

        // Draw QR code
        foreach ($matrix as $y => $row) {
            foreach ($row as $x => $cell) {
                if ($cell) {
                    $x1 = $margin + $x * $cellSize;
                    $y1 = $margin + $y * $cellSize;
                    $x2 = $x1 + $cellSize;
                    $y2 = $y1 + $cellSize;
                    imagefilledrectangle($image, $x1, $y1, $x2, $y2, $black);
                }
            }
        }

        // Output as PNG
        ob_start();
        imagepng($image);
        $imageData = ob_get_contents();
        ob_end_clean();

        imagedestroy($image);

        return $imageData;
    }

    /**
     * Save QR code to file
     */
    public static function saveToFile($data, $filename, $size = 300) {
        $imageData = self::generate($data, $size);

        // Create directory if not exists
        $dir = dirname($filename);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        file_put_contents($filename, $imageData);

        return [
            'filename' => basename($filename),
            'filepath' => $filename,
            'size' => strlen($imageData)
        ];
    }

    /**
     * Output QR code directly to browser
     */
    public static function output($data, $size = 300) {
        $imageData = self::generate($data, $size);

        header('Content-Type: image/png');
        header('Content-Length: ' . strlen($imageData));
        echo $imageData;
        exit;
    }
}
