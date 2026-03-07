<?php

$tests = [
    'tests/Feature/FinalQATest.php',
    'tests/Feature/AdminUserTest.php',
    'tests/Feature/AdminProductTest.php',
    'tests/Feature/AdminFormTest.php',
    'tests/Feature/JoinFormTest.php',
    'tests/Feature/AdminResetPasswordTest.php',
    'tests/Feature/DashboardTest.php',
    'tests/Unit/NotifierTest.php',
];

echo "========================================\n";
echo "    STARTING COMPREHENSIVE QA SUITE     \n";
echo "========================================\n\n";

$failedTests = [];

foreach ($tests as $testFile) {
    echo "Running $testFile...\n";
    echo "----------------------------------------\n";
    
    // Execute via shell_exec to isolate scope and handle exit() calls
    $output = shell_exec("php " . __DIR__ . "/" . $testFile);
    
    echo $output;
    echo "\n----------------------------------------\n";
    
    // Simple check for failure keywords in output
    if (strpos($output, 'FAIL') !== false || strpos($output, 'FATAL ERROR') !== false || strpos($output, 'Exception') !== false) {
        $failedTests[] = $testFile;
    }
}

echo "\n========================================\n";
echo "           QA SUITE FINISHED            \n";
echo "========================================\n";

if (empty($failedTests)) {
    echo "RESULT: ALL TESTS PASSED! \u{2705}\n";
    exit(0);
} else {
    echo "RESULT: SOME TESTS FAILED! \u{274C}\n";
    echo "Failed Tests:\n";
    foreach ($failedTests as $failed) {
        echo "- $failed\n";
    }
    exit(1);
}
