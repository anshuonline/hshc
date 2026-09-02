<?php
session_start();
include 'config/db.php';

// Redirect to login if not logged in
if (!isset($_SESSION['user_logged_in']) || !$_SESSION['user_logged_in']) {
    header('Location: login.php');
    exit;
}

if (!isset($_GET['id'])) {
    die("Invalid booking ID");
}

$booking_id = $_GET['id'];

// Fetch the specific booking
$stmt = $pdo->prepare("SELECT b.*, r.name as room_name, u.name, u.email, u.phone FROM bookings b 
                       LEFT JOIN rooms r ON b.room_id = r.id 
                       LEFT JOIN users u ON b.user_id = u.id 
                       WHERE b.id = ? AND b.user_id = ?");
$stmt->execute([$booking_id, $_SESSION['user_id']]);
$booking = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$booking) {
    die("Booking not found or you don't have permission to view it.");
}

$check_in = new DateTime($booking['check_in']);
$check_out = new DateTime($booking['check_out']);
$nights = $check_in->diff($check_out)->days;
$issue_date = date('d M Y, h:i A');

// Generate QR Code data (All details)
$qr_data = "Booking ID: " . $booking['booking_number'] . "\n";
$qr_data .= "Name: " . $booking['name'] . "\n";
$qr_data .= "Room: " . ($booking['room_name'] ?? 'Luxury Suite') . "\n";
$qr_data .= "Check-in: " . $check_in->format('d M Y') . "\n";
$qr_data .= "Check-out: " . $check_out->format('d M Y') . "\n";
$qr_data .= "Total: Rs." . number_format($booking['total_price'], 2);
$qr_url = "https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=" . urlencode($qr_data);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>E-Ticket - <?php echo htmlspecialchars($booking['booking_number']); ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,400;0,600;0,700;1,400&family=Montserrat:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background-color: #f1f5f9;
            color: #1e293b;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }

        /* A4 Page Setup */
        .a4-page {
            width: 210mm;
            min-height: 275mm;
            margin: 2rem auto;
            background: white;
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
            position: relative;
        }

        .gold-accent { color: #b48608; }
        .gold-border { border-color: #b48608; }
        
        .dashed-divider {
            height: 0;
            border-bottom: 2px dashed #cbd5e1;
            margin: 2rem 0;
            position: relative;
        }
        
        .dashed-divider::before, .dashed-divider::after {
            content: '';
            position: absolute;
            top: 50%;
            width: 20px;
            height: 20px;
            background-color: #f1f5f9;
            border-radius: 50%;
            transform: translateY(-50%);
            z-index: 10;
            border: 1px solid #e2e8f0;
        }
        
        .dashed-divider::before {
            left: -30px;
            border-left-color: transparent;
        }
        
        .dashed-divider::after {
            right: -30px;
            border-right-color: transparent;
        }

        @media print {
            body {
                background-color: white !important;
            }
            .dashed-divider::before, .dashed-divider::after {
                background-color: white !important;
            }
            .a4-page {
                margin: 0;
                box-shadow: none;
                width: 100%;
                min-height: 100%;
                border: none !important;
            }
            .no-print {
                display: none !important;
            }
            @page {
                size: A4 portrait;
                margin: 10mm;
            }
        }
        
        /* Mobile responsivenes (On screens smaller than A4) */
        @media (max-width: 210mm) {
            .a4-page {
                width: 100%;
                margin: 0;
                min-height: 100vh;
            }
        }
    </style>
</head>
<body class="font-sans">

    <!-- Action Buttons -->
    <div class="fixed bottom-8 right-8 flex gap-4 no-print z-50">
        <button onclick="window.close()" class="px-4 md:px-6 py-2 md:py-3 border border-slate-300 text-slate-700 bg-white hover:bg-slate-50 transition-colors uppercase tracking-widest text-xs font-semibold shadow-sm">
            Close
        </button>
        <button onclick="downloadPDF()" class="px-4 md:px-6 py-2 md:py-3 bg-[#d4af37] text-black hover:bg-[#aa8c2c] transition-colors uppercase tracking-widest text-xs font-bold shadow-lg shadow-black/10">
            <i class="fas fa-download mr-2"></i> Download PDF
        </button>
    </div>

    <!-- A4 Document -->
    <div class="a4-page overflow-hidden border border-slate-200">
        
        <!-- Header -->
        <div class="px-6 md:px-10 py-6 md:py-8 flex flex-col md:flex-row justify-between items-center border-b border-slate-100 gap-4">
            <div class="text-center md:text-left">
                <h1 class="text-3xl font-serif font-bold tracking-widest uppercase text-slate-900">
                    Grand<span class="font-light gold-accent ml-1">Luxe</span>
                </h1>
                <p class="text-[0.65rem] uppercase tracking-widest text-slate-500 mt-1 font-semibold">Official E-Ticket & Itinerary</p>
            </div>
            <div class="text-center md:text-right">
                <p class="text-sm font-bold text-slate-800">1-800-GRAND-LUXE</p>
                <p class="text-xs text-slate-500">inquiries@grandluxe.demo</p>
                <p class="text-xs text-slate-500">The Grand Avenue, Metropolis City</p>
            </div>
        </div>

        <!-- Banner Image -->
        <div class="w-full h-24 md:h-32 relative overflow-hidden bg-slate-900">
            <img src="images/hero_home.jpg" alt="Hotel Banner" class="w-full h-full object-cover opacity-60 mix-blend-overlay">
            <div class="absolute inset-0 flex flex-col justify-center px-6 md:px-10">
                <p class="text-white text-[0.65rem] md:text-xs uppercase tracking-widest mb-1 font-semibold">Confirmation Number</p>
                <p class="text-white text-xl md:text-2xl font-mono tracking-widest font-bold"><?php echo htmlspecialchars($booking['booking_number']); ?></p>
            </div>
        </div>

        <!-- Main Ticket Details -->
        <div class="px-6 md:px-10 py-8">
            <div class="flex flex-col md:flex-row justify-between gap-8 mb-8">
                <!-- Guest Info -->
                <div class="flex-1">
                    <p class="text-[0.65rem] text-slate-400 uppercase tracking-widest mb-1 font-bold">Guest Details</p>
                    <h2 class="text-xl font-serif text-slate-900 font-bold mb-2"><?php echo htmlspecialchars($booking['name']); ?></h2>
                    <p class="text-sm text-slate-600 mb-1"><i class="fas fa-envelope w-4 text-slate-400"></i> <?php echo htmlspecialchars($booking['email']); ?></p>
                    <?php if(!empty($booking['phone'])): ?>
                        <p class="text-sm text-slate-600"><i class="fas fa-phone w-4 text-slate-400"></i> <?php echo htmlspecialchars($booking['phone']); ?></p>
                    <?php endif; ?>
                </div>

                <!-- QR Code (Now contains all details) -->
                <div class="flex flex-col items-center justify-center shrink-0 border border-slate-200 p-3 rounded-lg bg-white shadow-sm mx-auto md:mx-0">
                    <img src="<?php echo $qr_url; ?>" alt="QR Code" class="w-24 h-24 object-cover">
                    <p class="text-[0.55rem] uppercase tracking-widest text-slate-400 mt-2 font-bold text-center">Scan for Entry</p>
                </div>
            </div>

            <!-- Booking Specifics -->
            <div class="border-2 border-dashed border-slate-300 rounded-lg overflow-hidden mb-8">
                <div class="bg-slate-50 px-6 py-4 border-b-2 border-dashed border-slate-300 flex flex-col md:flex-row justify-between items-center gap-2">
                    <p class="text-xs font-bold text-slate-500 uppercase tracking-wider">Accommodation</p>
                    <p class="text-lg font-serif font-bold gold-accent"><?php echo htmlspecialchars($booking['room_name'] ?? 'Luxury Suite'); ?></p>
                </div>
                
                <div class="grid grid-cols-2 md:grid-cols-4 divide-y md:divide-y-0 md:divide-x divide-slate-200">
                    <div class="p-5 text-center">
                        <p class="text-[0.65rem] text-slate-400 uppercase tracking-widest mb-2 font-bold">Check-in</p>
                        <p class="text-sm font-bold text-slate-800"><?php echo $check_in->format('d M Y'); ?></p>
                        <p class="text-xs text-slate-500 mt-1">14:00 Hrs</p>
                    </div>
                    <div class="p-5 text-center">
                        <p class="text-[0.65rem] text-slate-400 uppercase tracking-widest mb-2 font-bold">Check-out</p>
                        <p class="text-sm font-bold text-slate-800"><?php echo $check_out->format('d M Y'); ?></p>
                        <p class="text-xs text-slate-500 mt-1">12:00 Hrs</p>
                    </div>
                    <div class="p-5 text-center">
                        <p class="text-[0.65rem] text-slate-400 uppercase tracking-widest mb-2 font-bold">Occupancy</p>
                        <p class="text-sm font-bold text-slate-800"><?php echo $booking['adults']; ?> Adults</p>
                        <p class="text-xs text-slate-500 mt-1"><?php echo $booking['children']; ?> Children</p>
                    </div>
                    <div class="p-5 text-center bg-slate-50 flex flex-col justify-center">
                        <p class="text-[0.65rem] text-slate-400 uppercase tracking-widest mb-1 font-bold">Total Paid</p>
                        <p class="text-lg font-bold text-slate-900">&#8377;<?php echo number_format($booking['total_price'], 2); ?></p>
                        <div class="inline-flex items-center justify-center mt-2 px-2 py-1 bg-green-100 text-green-700 rounded text-[0.6rem] font-bold tracking-widest uppercase mx-auto">
                            Confirmed
                        </div>
                    </div>
                </div>
            </div>

            <!-- Rules and Guidelines -->
            <div class="dashed-divider"></div>

            <div class="mb-4">
                <h3 class="text-sm font-bold text-slate-800 uppercase tracking-widest mb-6 flex items-center">
                    <i class="fas fa-info-circle mr-2 text-slate-400"></i> Terms & Conditions
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-6">
                    <div class="flex items-start">
                        <div class="bg-slate-100 p-2 rounded-full mr-3 mt-0.5"><i class="fas fa-id-card text-slate-500 text-xs w-3 text-center"></i></div>
                        <p class="text-xs text-slate-600 leading-relaxed"><strong>Valid ID Required:</strong> All guests must present a valid government-issued photo ID (Passport, Aadhar, Driving License) upon check-in for verification.</p>
                    </div>
                    <div class="flex items-start">
                        <div class="bg-slate-100 p-2 rounded-full mr-3 mt-0.5"><i class="fas fa-clock text-slate-500 text-xs w-3 text-center"></i></div>
                        <p class="text-xs text-slate-600 leading-relaxed"><strong>Timings:</strong> Standard check-in time is 14:00 Hrs and check-out time is 12:00 Hrs. Early check-in and late check-out are subject to availability.</p>
                    </div>
                    <div class="flex items-start">
                        <div class="bg-slate-100 p-2 rounded-full mr-3 mt-0.5"><i class="fas fa-ban text-slate-500 text-xs w-3 text-center"></i></div>
                        <p class="text-xs text-slate-600 leading-relaxed"><strong>Cancellation Policy:</strong> Cancellations made 48 hours prior to the check-in date are fully refundable. No-shows will be charged 100% of the booking amount.</p>
                    </div>
                    <div class="flex items-start">
                        <div class="bg-slate-100 p-2 rounded-full mr-3 mt-0.5"><i class="fas fa-smoking-ban text-slate-500 text-xs w-3 text-center"></i></div>
                        <p class="text-xs text-slate-600 leading-relaxed"><strong>Property Rules:</strong> All rooms and enclosed public areas are strictly non-smoking. Pets are not allowed unless specified in the room details.</p>
                    </div>
                </div>
            </div>
            
        </div>

        <!-- Footer / Watermark -->
        <div class="absolute bottom-6 left-0 w-full text-center px-6 md:px-10 opacity-70">
            <p class="text-[0.65rem] text-slate-400 uppercase tracking-widest font-bold mb-1">Document Generated on <?php echo $issue_date; ?></p>
            <p class="text-[0.6rem] text-slate-300">This is a system generated electronic ticket. Physical signature is not required.</p>
        </div>

    </div>
    
    <script>
        function downloadPDF() {
            const element = document.querySelector('.a4-page');
            const opt = {
                margin:       0,
                filename:     'E-Ticket_<?php echo $booking['booking_number']; ?>.pdf',
                image:        { type: 'jpeg', quality: 0.98 },
                html2canvas:  { scale: 2, useCORS: true },
                jsPDF:        { unit: 'mm', format: 'a4', orientation: 'portrait' }
            };
            
            // Start download
            html2pdf().set(opt).from(element).save();
        }

        // Automatically download PDF when the page loads
        window.onload = function() {
            setTimeout(function() {
                downloadPDF();
            }, 800); // Small delay to ensure styles and images (QR code) are fully loaded
        };
    </script>
</body>
</html>
