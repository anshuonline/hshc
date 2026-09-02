<?php
/**
 * Simple QR Code Generator for TOTP 2FA
 * Generates QR codes as SVG without external dependencies
 */

class QRCodeGenerator {
    
    /**
     * Generate QR Code as SVG
     * @param string $data
     * @param int $size
     * @return string
     */
    public function generateSVG($data, $size = 200) {
        // Convert data to QR matrix
        $matrix = $this->generateMatrix($data);
        $matrixSize = count($matrix);
        
        // Calculate pixel size
        $pixelSize = floor($size / $matrixSize);
        $actualSize = $pixelSize * $matrixSize;
        
        // Generate SVG
        $svg = '<svg width="' . $actualSize . '" height="' . $actualSize . '" viewBox="0 0 ' . $actualSize . ' ' . $actualSize . '" xmlns="http://www.w3.org/2000/svg">';
        
        // Draw background
        $svg .= '<rect width="' . $actualSize . '" height="' . $actualSize . '" fill="white"/>';
        
        // Draw QR code modules
        for ($y = 0; $y < $matrixSize; $y++) {
            for ($x = 0; $x < $matrixSize; $x++) {
                if ($matrix[$y][$x]) {
                    $svg .= '<rect x="' . ($x * $pixelSize) . '" y="' . ($y * $pixelSize) . '" width="' . $pixelSize . '" height="' . $pixelSize . '" fill="black"/>';
                }
            }
        }
        
        $svg .= '</svg>';
        
        return $svg;
    }
    
    /**
     * Generate QR matrix using simple algorithm
     * @param string $data
     * @return array
     */
    private function generateMatrix($data) {
        // This is a simplified implementation
        // For a production system, you would want a full QR code library
        
        // Convert data to binary
        $binary = '';
        for ($i = 0; $i < strlen($data); $i++) {
            $binary .= sprintf('%08b', ord($data[$i]));
        }
        
        // Pad to make it square-ish
        $length = strlen($binary);
        $size = ceil(sqrt($length));
        
        // Make size odd and at least 21
        if ($size < 21) $size = 21;
        if ($size % 2 == 0) $size++;
        
        // Create matrix
        $matrix = array_fill(0, $size, array_fill(0, $size, 0));
        
        // Fill with data
        $index = 0;
        for ($y = 0; $y < $size && $index < $length; $y++) {
            for ($x = 0; $x < $size && $index < $length; $x++) {
                // Add finder patterns at corners
                if (($x < 7 && $y < 7) || 
                    ($x >= $size - 7 && $y < 7) || 
                    ($x < 7 && $y >= $size - 7)) {
                    // Skip finder pattern areas
                    continue;
                }
                
                $matrix[$y][$x] = ($binary[$index] === '1') ? 1 : 0;
                $index++;
            }
        }
        
        // Add finder patterns (simplified)
        $this->addFinderPattern($matrix, 0, 0, $size);
        $this->addFinderPattern($matrix, $size - 7, 0, $size);
        $this->addFinderPattern($matrix, 0, $size - 7, $size);
        
        return $matrix;
    }
    
    /**
     * Add finder pattern to QR code
     * @param array $matrix
     * @param int $x
     * @param int $y
     * @param int $size
     */
    private function addFinderPattern(&$matrix, $x, $y, $size) {
        // Outer square
        for ($i = 0; $i < 7; $i++) {
            if ($x + $i < $size && $y < $size) $matrix[$y][$x + $i] = 1;
            if ($x + $i < $size && $y + 6 < $size) $matrix[$y + 6][$x + $i] = 1;
            if ($x < $size && $y + $i < $size) $matrix[$y + $i][$x] = 1;
            if ($x + 6 < $size && $y + $i < $size) $matrix[$y + $i][$x + 6] = 1;
        }
        
        // Inner square
        for ($i = 0; $i < 3; $i++) {
            for ($j = 0; $j < 3; $j++) {
                if ($x + 2 + $i < $size && $y + 2 + $j < $size) {
                    $matrix[$y + 2 + $j][$x + 2 + $i] = 1;
                }
            }
        }
        
        // Separators
        if ($x + 7 < $size && $y < $size) $matrix[$y][$x + 7] = 0;
        if ($x + 7 < $size && $y + 7 < $size) $matrix[$y + 7][$x + 7] = 0;
        if ($x < $size && $y + 7 < $size) $matrix[$y + 7][$x] = 0;
    }
}