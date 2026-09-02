<?php
/**
 * Google Authenticator TOTP Implementation
 * This implementation is specifically designed for Google Authenticator compatibility
 */

class GoogleAuthenticator {
    /**
     * Generate a secret for TOTP (Time-based One-Time Password)
     * @return string
     */
    public function generateSecret() {
        // Generate a random base32 secret
        // Using 160 bits (20 bytes) as per RFC 4226
        $randomBytes = random_bytes(20);
        return $this->base32Encode($randomBytes);
    }
    
    /**
     * Generate a QR code URL for manual setup
     * @param string $email
     * @param string $secret
     * @param string $issuer
     * @return string
     */
    public function getQrCodeUrl($email, $secret, $issuer = 'Demo Hotel & Resort') {
        // Properly format the QR code URL for TOTP
        $label = urlencode("$issuer:$email");
        $secretEncoded = urlencode($secret);
        $issuerEncoded = urlencode($issuer);
        return "otpauth://totp/$label?secret=$secretEncoded&issuer=$issuerEncoded";
    }
    
    /**
     * Verify a TOTP token
     * @param string $secret
     * @param string $token
     * @param int $discrepancy
     * @return bool
     */
    public function verifyToken($secret, $token, $discrepancy = 2) {
        // Allow for more time discrepancy to account for clock drift
        $currentTime = floor(time() / 30);
        
        for ($i = -$discrepancy; $i <= $discrepancy; $i++) {
            $time = $currentTime + $i;
            $generatedToken = $this->getCode($secret, $time);
            
            if (hash_equals($generatedToken, $token)) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Generate a TOTP code
     * @param string $secret
     * @param int $time
     * @return string
     */
    public function getCode($secret, $time = null) {
        // Ensure secret is uppercase for compatibility
        $secret = strtoupper($secret);
        $secret = $this->base32Decode($secret);
        
        if ($time === null) {
            $time = floor(time() / 30);
        }
        
        // Pack time as big-endian 64-bit integer (8 bytes)
        // Fix: Use proper 64-bit packing
        $timeBytes = pack('J', $time); // J = unsigned 64-bit big-endian
        $hash = hash_hmac('sha1', $timeBytes, $secret, true);
        $offset = ord($hash[19]) & 0x0f;
        $code = (
                ((ord($hash[$offset + 0]) & 0x7f) << 24) |
                ((ord($hash[$offset + 1]) & 0xff) << 16) |
                ((ord($hash[$offset + 2]) & 0xff) << 8) |
                (ord($hash[$offset + 3]) & 0xff)
            ) % pow(10, 6);
        
        return str_pad($code, 6, '0', STR_PAD_LEFT);
    }
    
    /**
     * Base32 encode
     * @param string $data
     * @return string
     */
    protected function base32Encode($data) {
        $alphabet = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
        $output = '';
        $buffer = 0;
        $bitsLeft = 0;
        
        for ($i = 0; $i < strlen($data); $i++) {
            $buffer = ($buffer << 8) | ord($data[$i]);
            $bitsLeft += 8;
            
            while ($bitsLeft >= 5) {
                $output .= $alphabet[($buffer >> ($bitsLeft - 5)) & 31];
                $bitsLeft -= 5;
            }
        }
        
        if ($bitsLeft > 0) {
            $output .= $alphabet[($buffer << (5 - $bitsLeft)) & 31];
        }
        
        // Pad to make length a multiple of 8
        while (strlen($output) % 8 !== 0) {
            $output .= '=';
        }
        
        return $output;
    }
    
    /**
     * Base32 decode
     * @param string $data
     * @return string
     */
    protected function base32Decode($data) {
        // Remove padding and convert to uppercase
        $data = rtrim(strtoupper($data), '=');
        $alphabet = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
        $output = '';
        $buffer = 0;
        $bitsLeft = 0;
        
        for ($i = 0; $i < strlen($data); $i++) {
            $char = $data[$i];
            $index = strpos($alphabet, $char);
            
            if ($index === false) {
                continue;
            }
            
            $buffer = ($buffer << 5) | $index;
            $bitsLeft += 5;
            
            if ($bitsLeft >= 8) {
                $output .= chr(($buffer >> ($bitsLeft - 8)) & 0xFF);
                $bitsLeft -= 8;
            }
        }
        
        return $output;
    }
    
    /**
     * Debug method to test base32 decode
     * @param string $data
     * @return string
     */
    public function debugBase32Decode($data) {
        return $this->base32Decode($data);
    }
}