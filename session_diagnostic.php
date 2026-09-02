<?php
// Session Diagnostic Test
session_start();

echo "Session Diagnostic Test\n";
echo "======================\n\n";

echo "1. Session Status:\n";
echo "   Session ID: " . session_id() . "\n";
echo "   Session Status: " . session_status() . "\n\n";

echo "2. Current Session Data:\n";
if (!empty($_SESSION)) {
    foreach ($_SESSION as $key => $value) {
        echo "   $key: " . (is_array($value) ? json_encode($value) : $value) . "\n";
    }
} else {
    echo "   No session data found\n";
}
echo "\n";

echo "3. Setting Test Session Data:\n";
$_SESSION['test_key'] = 'test_value';
$_SESSION['test_time'] = time();
$_SESSION['test_array'] = ['a' => 1, 'b' => 2];
echo "   Set test data in session\n\n";

echo "4. Verifying Session Data:\n";
echo "   test_key: " . ($_SESSION['test_key'] ?? 'NOT SET') . "\n";
echo "   test_time: " . ($_SESSION['test_time'] ?? 'NOT SET') . "\n";
echo "   test_array: " . (isset($_SESSION['test_array']) ? json_encode($_SESSION['test_array']) : 'NOT SET') . "\n\n";

echo "5. Session Configuration:\n";
echo "   Session Save Path: " . session_save_path() . "\n";
echo "   Session Name: " . session_name() . "\n";
echo "   Session Cookie Params: " . json_encode(session_get_cookie_params()) . "\n\n";

// Test session persistence
if (isset($_GET['step']) && $_GET['step'] == '2') {
    echo "6. Session Persistence Test (Step 2):\n";
    echo "   test_key: " . ($_SESSION['test_key'] ?? 'NOT SET') . "\n";
    echo "   test_time: " . ($_SESSION['test_time'] ?? 'NOT SET') . "\n";
    echo "   test_array: " . (isset($_SESSION['test_array']) ? json_encode($_SESSION['test_array']) : 'NOT SET') . "\n\n";
    
    if (isset($_SESSION['test_key'])) {
        echo "✓ Session persistence test PASSED\n";
    } else {
        echo "✗ Session persistence test FAILED\n";
    }
} else {
    echo "6. Session Persistence Test:\n";
    echo "   Please <a href='?step=2'>click here</a> to test session persistence\n\n";
}

echo "Test completed.\n";
?>