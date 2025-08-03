<?php

namespace App\Services;

use Exception;

class QRCodeService
{
    private $uploadPath;

    public function __construct()
    {
        $this->uploadPath = WRITEPATH . 'uploads/qr/';
        
        // Create directory if it doesn't exist
        if (!is_dir($this->uploadPath)) {
            mkdir($this->uploadPath, 0755, true);
        }
    }

    /**
     * Generate QR Code
     */
    public function generate($data, $type = 'tiket', $size = 200)
    {
        try {
            // For now, we'll create a simple placeholder
            // In production, you would use a library like endroid/qr-code
            
            $filename = $type . '_' . md5($data . time()) . '.png';
            $filePath = $this->uploadPath . $filename;
            
            // Create a simple placeholder image
            $image = imagecreate($size, $size);
            $bg = imagecolorallocate($image, 255, 255, 255);
            $black = imagecolorallocate($image, 0, 0, 0);
            
            // Add some basic QR-like pattern
            for ($i = 0; $i < $size; $i += 10) {
                for ($j = 0; $j < $size; $j += 10) {
                    if (rand(0, 1)) {
                        imagefilledrectangle($image, $i, $j, $i + 8, $j + 8, $black);
                    }
                }
            }
            
            // Add text
            imagestring($image, 3, 10, $size - 20, substr($data, 0, 20), $black);
            
            imagepng($image, $filePath);
            imagedestroy($image);
            
            return 'uploads/qr/' . $filename;
            
        } catch (Exception $e) {
            log_message('error', 'QR Code generation failed: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Generate QR Code with URL
     */
    public function generateWithURL($url, $type = 'tiket', $size = 200)
    {
        return $this->generate($url, $type, $size);
    }

    /**
     * Generate ticket QR code
     */
    public function generateTicketQR($ticketCode)
    {
        $verifyUrl = base_url("cek-tiket?kode=" . $ticketCode);
        return $this->generateWithURL($verifyUrl, 'tiket');
    }

    /**
     * Generate certificate verification QR code
     */
    public function generateCertificateQR($certificateNumber)
    {
        $verifyUrl = base_url("verify-certificate?nomor=" . $certificateNumber);
        return $this->generateWithURL($verifyUrl, 'sertifikat');
    }

    /**
     * Delete QR code file
     */
    public function delete($filePath)
    {
        $fullPath = WRITEPATH . $filePath;
        
        if (file_exists($fullPath)) {
            return unlink($fullPath);
        }
        
        return false;
    }

    /**
     * Get QR code URL
     */
    public function getURL($filePath)
    {
        if ($filePath && file_exists(WRITEPATH . $filePath)) {
            return base_url($filePath);
        }
        
        return null;
    }

    /**
     * Validate QR code file
     */
    public function validate($filePath)
    {
        if (!$filePath) return false;
        
        $fullPath = WRITEPATH . $filePath;
        return file_exists($fullPath) && is_readable($fullPath);
    }

    /**
     * Get file size
     */
    public function getFileSize($filePath)
    {
        if (!$this->validate($filePath)) {
            return 0;
        }
        
        return filesize(WRITEPATH . $filePath);
    }

    /**
     * Clean old QR codes (for maintenance)
     */
    public function cleanOldFiles($days = 30)
    {
        $files = glob($this->uploadPath . '*.png');
        $deleted = 0;
        
        foreach ($files as $file) {
            if (filemtime($file) < strtotime("-{$days} days")) {
                if (unlink($file)) {
                    $deleted++;
                }
            }
        }
        
        return $deleted;
    }
}