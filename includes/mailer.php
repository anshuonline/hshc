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
            <div style='background-color: #0f172a; border: 1px solid #1e293b; padding: 15px; margin: 30px 0; border-radius: 4px;'>
                <p style='margin: 0 0 15px 0; color: #d4af37; font-family: Georgia, serif; font-size: 20px;'>Itinerary Details</p>
                
                <table style='width: 100%; border-collapse: collapse; font-size: 14px; color: #f1f5f9; table-layout: fixed;'>
                    <tr>
                        <td style='padding: 8px 0; color: #94a3b8; width: 35%;'>Confirmation No:</td>
                        <td style='padding: 8px 0; font-family: monospace; font-size: 15px; font-weight: bold; word-break: break-all; word-wrap: break-word; overflow-wrap: break-word;'>{$bookingDetails['booking_number']}</td>
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

/**
 * Sends an email to user when admin changes booking status (pending/confirmed/cancelled)
 */
function sendBookingStatusEmail($userEmail, $userName, $bookingNumber, $newStatus, $bookingData) {
    $fromEmail = 'info@yourwebsite.com';

    $statusConfig = [
        'confirmed' => ['color' => '#22c55e', 'icon' => '✅', 'label' => 'Confirmed', 'msg' => 'Great news! Your reservation has been <strong style="color:#22c55e;">confirmed</strong> by our team. We look forward to welcoming you.'],
        'cancelled'  => ['color' => '#ef4444', 'icon' => '❌', 'label' => 'Cancelled',  'msg' => 'We regret to inform you that your reservation has been <strong style="color:#ef4444;">cancelled</strong>. Please contact us if you have any questions.'],
        'pending'    => ['color' => '#f59e0b', 'icon' => '⏳', 'label' => 'Pending',    'msg' => 'Your reservation is currently <strong style="color:#f59e0b;">pending review</strong>. Our team will update you shortly.'],
    ];

    $cfg = $statusConfig[$newStatus] ?? $statusConfig['pending'];
    $subject = 'Booking Update — ' . $cfg['label'] . ' (Ref: ' . $bookingNumber . ')';

    $headers  = "MIME-Version: 1.0\r\n";
    $headers .= "Content-type:text/html;charset=UTF-8\r\n";
    $headers .= "From: Grand Luxe Hotel <" . $fromEmail . ">\r\n";
    $headers .= "Reply-To: " . $fromEmail . "\r\n";

    $checkIn  = !empty($bookingData['check_in'])  ? date('F d, Y', strtotime($bookingData['check_in']))  : 'N/A';
    $checkOut = !empty($bookingData['check_out']) ? date('F d, Y', strtotime($bookingData['check_out'])) : 'N/A';

    $htmlBody = "
    <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; background-color: #020617; color: #ffffff;'>
        <!-- Header -->
        <div style='text-align: center; padding: 40px 20px; border-bottom: 2px solid {$cfg['color']};'>
            <h1 style='font-family: Georgia, serif; margin: 0; font-size: 26px; letter-spacing: 4px; text-transform: uppercase;'>Grand<span style='color: #d4af37; font-weight: normal;'>Luxe</span></h1>
            <p style='color: #94a3b8; font-size: 11px; letter-spacing: 3px; text-transform: uppercase; margin-top: 8px;'>Booking Status Update</p>
        </div>

        <!-- Status Badge -->
        <div style='text-align: center; padding: 30px 20px 10px;'>
            <span style='display:inline-block; padding: 10px 28px; background-color: {$cfg['color']}22; border: 1px solid {$cfg['color']}; color: {$cfg['color']}; font-size: 13px; letter-spacing: 3px; text-transform: uppercase; font-weight: bold;'>{$cfg['icon']} &nbsp;{$cfg['label']}</span>
        </div>

        <!-- Body -->
        <div style='padding: 30px 40px 40px;'>
            <h2 style='font-family: Georgia, serif; font-size: 20px; margin-top: 0;'>Dear {$userName},</h2>
            <p style='color: #cbd5e1; font-size: 15px; line-height: 1.7; font-weight: 300;'>{$cfg['msg']}</p>

            <!-- Booking Info -->
            <div style='background-color: #0f172a; border: 1px solid #1e293b; padding: 15px; margin: 25px 0;'>
                <p style='margin: 0 0 15px; color: #d4af37; font-family: Georgia, serif; font-size: 16px;'>Reservation Details</p>
                <table style='width: 100%; border-collapse: collapse; font-size: 14px; color: #f1f5f9; table-layout: fixed;'>
                    <tr><td style='padding: 7px 0; color: #94a3b8; width: 35%;'>Booking Ref:</td><td style='font-family: monospace; font-weight: bold; font-size: 15px; word-break: break-all; word-wrap: break-word; overflow-wrap: break-word;'>{$bookingNumber}</td></tr>
                    <tr><td style='padding: 7px 0; color: #94a3b8;'>Check-in:</td><td>{$checkIn}</td></tr>
                    <tr><td style='padding: 7px 0; color: #94a3b8;'>Check-out:</td><td>{$checkOut}</td></tr>
                    <tr><td style='padding: 7px 0; color: #94a3b8; border-top: 1px dashed #334155;'>New Status:</td><td style='color: {$cfg['color']}; font-weight: bold; border-top: 1px dashed #334155;'>{$cfg['label']}</td></tr>
                </table>
            </div>

            <p style='color: #94a3b8; font-size: 13px; line-height: 1.7;'>If you have any questions, please contact our concierge team and quote your booking reference.</p>
        </div>

        <!-- Spam notice -->
        <div style='text-align: center; padding: 12px; background: #0a0a0a; border-top: 1px dashed #1e293b;'>
            <p style='color: #475569; font-size: 11px; margin: 0;'>If this arrived in Spam, please mark it <strong>Not Spam</strong>.</p>
        </div>

        <!-- Footer -->
        <div style='text-align: center; padding: 25px; background: #020617; border-top: 1px solid #1e293b;'>
            <p style='color: #64748b; font-size: 11px; text-transform: uppercase; letter-spacing: 1px; margin: 0 0 4px;'>Grand Luxe Hotel &amp; Spa</p>
            <p style='color: #64748b; font-size: 11px; margin: 0;'>The Grand Avenue, Metropolis City | 1-800-GRAND-LUXE</p>
        </div>
    </div>
    ";

    return @mail($userEmail, $subject, $htmlBody, $headers);
}

/**
 * Sends an email to user when admin changes payment status (pending/paid/failed)
 */
function sendPaymentStatusEmail($userEmail, $userName, $bookingNumber, $newPaymentStatus, $bookingData) {
    $fromEmail = 'info@yourwebsite.com';

    $statusConfig = [
        'paid'    => ['color' => '#22c55e', 'icon' => '💳', 'label' => 'Payment Received', 'msg' => 'We are pleased to confirm that your payment has been <strong style="color:#22c55e;">received and processed</strong>. Your booking is now financially secured.'],
        'failed'  => ['color' => '#ef4444', 'icon' => '⚠️', 'label' => 'Payment Failed',   'msg' => 'Unfortunately, your payment has <strong style="color:#ef4444;">failed</strong>. Please contact us immediately to avoid cancellation of your reservation.'],
        'pending' => ['color' => '#f59e0b', 'icon' => '⏳', 'label' => 'Payment Pending',  'msg' => 'Your payment is currently <strong style="color:#f59e0b;">pending</strong>. Please ensure payment is completed to confirm your reservation.'],
    ];

    $cfg = $statusConfig[$newPaymentStatus] ?? $statusConfig['pending'];
    $subject = 'Payment Update — ' . $cfg['label'] . ' (Ref: ' . $bookingNumber . ')';

    $headers  = "MIME-Version: 1.0\r\n";
    $headers .= "Content-type:text/html;charset=UTF-8\r\n";
    $headers .= "From: Grand Luxe Hotel <" . $fromEmail . ">\r\n";
    $headers .= "Reply-To: " . $fromEmail . "\r\n";

    $totalPrice = !empty($bookingData['total_price']) ? '₹' . number_format($bookingData['total_price'], 2) : 'N/A';

    $htmlBody = "
    <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; background-color: #020617; color: #ffffff;'>
        <!-- Header -->
        <div style='text-align: center; padding: 40px 20px; border-bottom: 2px solid {$cfg['color']};'>
            <h1 style='font-family: Georgia, serif; margin: 0; font-size: 26px; letter-spacing: 4px; text-transform: uppercase;'>Grand<span style='color: #d4af37; font-weight: normal;'>Luxe</span></h1>
            <p style='color: #94a3b8; font-size: 11px; letter-spacing: 3px; text-transform: uppercase; margin-top: 8px;'>Payment Status Update</p>
        </div>

        <!-- Status Badge -->
        <div style='text-align: center; padding: 30px 20px 10px;'>
            <span style='display:inline-block; padding: 10px 28px; background-color: {$cfg['color']}22; border: 1px solid {$cfg['color']}; color: {$cfg['color']}; font-size: 13px; letter-spacing: 3px; text-transform: uppercase; font-weight: bold;'>{$cfg['icon']} &nbsp;{$cfg['label']}</span>
        </div>

        <!-- Body -->
        <div style='padding: 30px 40px 40px;'>
            <h2 style='font-family: Georgia, serif; font-size: 20px; margin-top: 0;'>Dear {$userName},</h2>
            <p style='color: #cbd5e1; font-size: 15px; line-height: 1.7; font-weight: 300;'>{$cfg['msg']}</p>

            <!-- Booking Info -->
            <div style='background-color: #0f172a; border: 1px solid #1e293b; padding: 15px; margin: 25px 0;'>
                <p style='margin: 0 0 15px; color: #d4af37; font-family: Georgia, serif; font-size: 16px;'>Payment Details</p>
                <table style='width: 100%; border-collapse: collapse; font-size: 14px; color: #f1f5f9; table-layout: fixed;'>
                    <tr><td style='padding: 7px 0; color: #94a3b8; width: 35%;'>Booking Ref:</td><td style='font-family: monospace; font-weight: bold; font-size: 15px; word-break: break-all; word-wrap: break-word; overflow-wrap: break-word;'>{$bookingNumber}</td></tr>
                    <tr><td style='padding: 7px 0; color: #94a3b8; border-top: 1px dashed #334155;'>Amount:</td><td style='border-top: 1px dashed #334155; color: #d4af37; font-weight: bold;'>{$totalPrice}</td></tr>
                    <tr><td style='padding: 7px 0; color: #94a3b8;'>Payment Status:</td><td style='color: {$cfg['color']}; font-weight: bold;'>{$cfg['label']}</td></tr>
                </table>
            </div>

            <p style='color: #94a3b8; font-size: 13px; line-height: 1.7;'>If you have any questions regarding this payment, please contact our team quoting your booking reference.</p>
        </div>

        <!-- Spam notice -->
        <div style='text-align: center; padding: 12px; background: #0a0a0a; border-top: 1px dashed #1e293b;'>
            <p style='color: #475569; font-size: 11px; margin: 0;'>If this arrived in Spam, please mark it <strong>Not Spam</strong>.</p>
        </div>

        <!-- Footer -->
        <div style='text-align: center; padding: 25px; background: #020617; border-top: 1px solid #1e293b;'>
            <p style='color: #64748b; font-size: 11px; text-transform: uppercase; letter-spacing: 1px; margin: 0 0 4px;'>Grand Luxe Hotel &amp; Spa</p>
            <p style='color: #64748b; font-size: 11px; margin: 0;'>The Grand Avenue, Metropolis City | 1-800-GRAND-LUXE</p>
        </div>
    </div>
    ";

    return @mail($userEmail, $subject, $htmlBody, $headers);
}
?>
