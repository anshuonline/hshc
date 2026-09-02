<?php
/**
 * Sends a premium booking confirmation email using native PHP mail() function
 * Works automatically on Hostinger without requiring SMTP credentials or passwords
 */
function sendBookingConfirmationEmail($userEmail, $userName, $bookingDetails) {
    // Subject
    $subject = 'Booking Confirmation - Grand Luxe Hotel (Ref: ' . $bookingDetails['booking_number'] . ')';

    // Set "From" email address. On Hostinger, as long as this is an email created in your hPanel (like info@yourdomain.com),
    // it will automatically send without needing a password.
    $fromEmail = 'info@yourwebsite.com'; 
    
    // Headers for HTML email
    $headers = "MIME-Version: 1.0" . "\r\n";
    $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
    $headers .= "From: Grand Luxe Hotel <" . $fromEmail . ">" . "\r\n";
    $headers .= "Reply-To: " . $fromEmail . "\r\n";
    
    // Generate dynamic URL for the E-Ticket
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://';
    $domain = $_SERVER['HTTP_HOST'];
    $requestUri = dirname($_SERVER['REQUEST_URI']);
    if (substr($requestUri, -1) === '/') {
        $requestUri = substr($requestUri, 0, -1);
    }
    // Link directly to the specific print-itinerary.php with the booking ID
    $ticketUrl = $protocol . $domain . $requestUri . '/print-itinerary.php?id=' . $bookingDetails['id'];
    
    // Variables for the email body
    $checkInDate = date('F d, Y', strtotime($bookingDetails['check_in']));
    $checkOutDate = date('F d, Y', strtotime($bookingDetails['check_out']));
    $totalPrice = number_format($bookingDetails['total_price'], 2);
    
    // Premium HTML Email Template
    $htmlBody = "
    <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; background-color: #020617; color: #ffffff; padding: 0;'>
        
        <!-- Header -->
        <div style='text-align: center; padding: 40px 20px; background: linear-gradient(135deg, #0f172a 0%, #020617 100%); border-bottom: 2px solid #d4af37;'>
            <h1 style='font-family: Georgia, serif; margin: 0; font-size: 28px; letter-spacing: 4px; text-transform: uppercase;'>Grand<span style='color: #d4af37; font-weight: normal;'>Luxe</span></h1>
            <p style='color: #94a3b8; font-size: 12px; letter-spacing: 2px; text-transform: uppercase; margin-top: 10px;'>Official Confirmation</p>
        </div>

        <!-- Body Content -->
        <div style='padding: 40px 30px; background-color: #030712;'>
            <h2 style='font-family: Georgia, serif; font-size: 22px; margin-top: 0; color: #ffffff;'>Dear {$userName},</h2>
            <p style='color: #cbd5e1; font-size: 15px; line-height: 1.6; font-weight: 300;'>
                We are delighted to confirm your reservation at Grand Luxe Hotel. Prepare yourself for an unforgettable experience of refined elegance and bespoke service.
            </p>

            <!-- Booking Details Box -->
            <div style='background-color: #0f172a; border: 1px solid #1e293b; padding: 25px; margin: 30px 0; border-radius: 4px;'>
                <p style='margin: 0 0 15px 0; color: #d4af37; font-family: Georgia, serif; font-size: 20px;'>Itinerary Details</p>
                
                <table style='width: 100%; border-collapse: collapse; font-size: 14px; color: #f1f5f9;'>
                    <tr>
                        <td style='padding: 8px 0; color: #94a3b8; width: 40%;'>Confirmation No:</td>
                        <td style='padding: 8px 0; font-family: monospace; font-size: 16px; font-weight: bold;'>{$bookingDetails['booking_number']}</td>
                    </tr>
                    <tr>
                        <td style='padding: 8px 0; color: #94a3b8;'>Check-in:</td>
                        <td style='padding: 8px 0; font-weight: bold;'>{$checkInDate} <span style='font-weight: normal; color: #94a3b8; font-size: 12px;'>(From 14:00)</span></td>
                    </tr>
                    <tr>
                        <td style='padding: 8px 0; color: #94a3b8;'>Check-out:</td>
                        <td style='padding: 8px 0; font-weight: bold;'>{$checkOutDate} <span style='font-weight: normal; color: #94a3b8; font-size: 12px;'>(Until 12:00)</span></td>
                    </tr>
                    <tr>
                        <td style='padding: 8px 0; color: #94a3b8;'>Occupancy:</td>
                        <td style='padding: 8px 0; font-weight: bold;'>{$bookingDetails['adults']} Adults, {$bookingDetails['children']} Children</td>
                    </tr>
                    <tr>
                        <td style='padding: 8px 0; color: #94a3b8; border-top: 1px dashed #334155;'>Total Amount:</td>
                        <td style='padding: 8px 0; color: #d4af37; font-size: 16px; font-weight: bold; border-top: 1px dashed #334155;'>₹{$totalPrice}</td>
                    </tr>
                </table>
            </div>

            <p style='color: #cbd5e1; font-size: 14px; line-height: 1.6; margin-bottom: 30px;'>
                You can view, print, or download your official e-ticket directly by clicking the link below. If you require any bespoke arrangements prior to your arrival, our concierge is at your service.
            </p>

            <div style='text-align: center;'>
                <a href='{$ticketUrl}' style='display: inline-block; padding: 14px 30px; background-color: #d4af37; color: #000000; text-decoration: none; font-weight: bold; font-size: 14px; text-transform: uppercase; letter-spacing: 2px;'>View / Download E-Ticket</a>
            </div>
        </div>

        <!-- Footer -->
        <div style='text-align: center; padding: 30px 20px; background-color: #020617; border-top: 1px solid #1e293b;'>
            <p style='color: #64748b; font-size: 11px; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 5px;'>Grand Luxe Hotel & Spa</p>
            <p style='color: #64748b; font-size: 11px; margin: 0;'>The Grand Avenue, Metropolis City | 1-800-GRAND-LUXE</p>
        </div>
        
    </div>
    ";

    // Send email using native mail() function
    // The @ symbol suppresses warnings if mail() is disabled on local environment
    return @mail($userEmail, $subject, $htmlBody, $headers);
}
?>
