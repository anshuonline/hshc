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

/**
 * Sends a premium welcome email to newly registered users
 */
function sendWelcomeEmail($userEmail, $userName) {
    $subject = 'Welcome to Grand Luxe Hotel — Your Exclusive Membership Awaits';

    $fromEmail = 'info@yourwebsite.com';

    $headers  = "MIME-Version: 1.0\r\n";
    $headers .= "Content-type:text/html;charset=UTF-8\r\n";
    $headers .= "From: Grand Luxe Hotel <" . $fromEmail . ">\r\n";
    $headers .= "Reply-To: " . $fromEmail . "\r\n";

    $firstName = explode(' ', trim($userName))[0];

    $htmlBody = "
    <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; background-color: #020617; color: #ffffff; padding: 0;'>

        <!-- Header -->
        <div style='text-align: center; padding: 50px 20px 30px; background: linear-gradient(160deg, #0f172a 0%, #020617 70%); border-bottom: 1px solid #d4af37;'>
            <p style='color: #d4af37; font-size: 11px; letter-spacing: 5px; text-transform: uppercase; margin: 0 0 16px 0;'>Grand Luxe Hotel</p>
            <h1 style='font-family: Georgia, serif; margin: 0; font-size: 32px; font-weight: normal; letter-spacing: 4px; text-transform: uppercase; color: #ffffff;'>Welcome, <span style='color: #d4af37; font-style: italic;'>{$firstName}</span></h1>
            <div style='width: 40px; height: 1px; background: #d4af37; margin: 20px auto 0;'></div>
        </div>

        <!-- Body -->
        <div style='padding: 50px 40px; background-color: #030712;'>
            <p style='color: #cbd5e1; font-size: 16px; line-height: 1.8; font-weight: 300; margin-top: 0;'>
                We are honoured to welcome you into the <strong style='color: #d4af37; font-weight: normal; font-style: italic;'>Grand Luxe</strong> family — a world where every moment is curated with the finest attention to detail.
            </p>
            <p style='color: #94a3b8; font-size: 14px; line-height: 1.8; font-weight: 300;'>
                Your membership unlocks a realm of bespoke privileges — from priority reservations to exclusive seasonal offers, curated only for our most valued guests.
            </p>

            <!-- Perks Box -->
            <div style='background-color: #0f172a; border-left: 2px solid #d4af37; padding: 25px 30px; margin: 35px 0;'>
                <p style='margin: 0 0 18px 0; color: #d4af37; font-family: Georgia, serif; font-size: 16px; letter-spacing: 2px; text-transform: uppercase;'>Your Member Benefits</p>
                <table style='width: 100%; font-size: 14px; color: #cbd5e1; border-collapse: collapse;'>
                    <tr><td style='padding: 8px 0; vertical-align: top;'><span style='color: #d4af37; margin-right: 10px;'>✦</span></td><td style='padding: 8px 0;'>Priority booking for exclusive suites &amp; packages</td></tr>
                    <tr><td style='padding: 8px 0; vertical-align: top;'><span style='color: #d4af37; margin-right: 10px;'>✦</span></td><td style='padding: 8px 0;'>Early access to seasonal offers &amp; festive specials</td></tr>
                    <tr><td style='padding: 8px 0; vertical-align: top;'><span style='color: #d4af37; margin-right: 10px;'>✦</span></td><td style='padding: 8px 0;'>Dedicated 24/7 concierge service</td></tr>
                    <tr><td style='padding: 8px 0; vertical-align: top;'><span style='color: #d4af37; margin-right: 10px;'>✦</span></td><td style='padding: 8px 0;'>Personalised in-room amenities on arrival</td></tr>
                </table>
            </div>

            <p style='color: #94a3b8; font-size: 14px; line-height: 1.8; font-weight: 300;'>
                Should you have any preferences or special requests ahead of your first visit, our concierge team is always at your service.
            </p>

            <div style='text-align: center; margin-top: 40px;'>
                <a href='#' style='display: inline-block; padding: 15px 35px; background-color: #d4af37; color: #000000; text-decoration: none; font-weight: bold; font-size: 12px; text-transform: uppercase; letter-spacing: 3px;'>Explore Rooms &amp; Book Now</a>
            </div>
        </div>

        <!-- Spam Notice -->
        <div style='text-align: center; padding: 15px 30px; background-color: #0a0a0a; border-top: 1px dashed #1e293b;'>
            <p style='color: #475569; font-size: 11px; margin: 0;'>If this email landed in your spam folder, please mark it as <strong>Not Spam</strong> to ensure you never miss an exclusive offer.</p>
        </div>

        <!-- Footer -->
        <div style='text-align: center; padding: 30px 20px; background-color: #020617; border-top: 1px solid #1e293b;'>
            <p style='color: #64748b; font-size: 11px; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 5px;'>Grand Luxe Hotel &amp; Spa</p>
            <p style='color: #64748b; font-size: 11px; margin: 0;'>The Grand Avenue, Metropolis City | 1-800-GRAND-LUXE</p>
        </div>
    </div>
    ";

    return @mail($userEmail, $subject, $htmlBody, $headers);
}
?>
