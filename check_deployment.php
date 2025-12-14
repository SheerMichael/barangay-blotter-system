<?php
/**
 * Production Readiness Checker
 * Run this script locally before deployment to verify everything is configured correctly
 * 
 * Usage: php check_deployment.php
 */

echo "==============================================\n";
echo "  Barangay Blotter - Deployment Checker\n";
echo "==============================================\n\n";

$errors = [];
$warnings = [];
$success = [];

// Check 1: Config files exist
echo "[1] Checking configuration files...\n";
if (file_exists(__DIR__ . '/.env.example')) {
    $success[] = "✓ .env.example exists";
} else {
    $errors[] = "✗ .env.example is missing";
}

if (file_exists(__DIR__ . '/.env')) {
    $success[] = "✓ .env exists";
    
    // Check .env content
    $envContent = file_get_contents(__DIR__ . '/.env');
    if (strpos($envContent, 'DB_HOST=') !== false) {
        $success[] = "✓ .env contains database configuration";
    } else {
        $errors[] = "✗ .env missing database configuration";
    }
    
    if (strpos($envContent, 'SMTP_HOST=') !== false) {
        $success[] = "✓ .env contains email configuration";
    } else {
        $warnings[] = "⚠ .env missing email configuration (optional)";
    }
} else {
    $errors[] = "✗ .env is missing (copy from .env.example)";
}

if (file_exists(__DIR__ . '/config/config.php')) {
    $success[] = "✓ config/config.php exists";
} else {
    $errors[] = "✗ config/config.php is missing";
}

if (file_exists(__DIR__ . '/config/error_handler.php')) {
    $success[] = "✓ config/error_handler.php exists";
} else {
    $errors[] = "✗ config/error_handler.php is missing";
}

// Check 2: Database files
echo "\n[2] Checking database files...\n";
if (file_exists(__DIR__ . '/database/database.php')) {
    $success[] = "✓ database/database.php exists";
    
    // Check for hardcoded credentials
    $dbContent = file_get_contents(__DIR__ . '/database/database.php');
    if (strpos($dbContent, 'Config::get') !== false) {
        $success[] = "✓ Database uses environment variables";
    } else {
        $errors[] = "✗ Database has hardcoded credentials";
    }
} else {
    $errors[] = "✗ database/database.php is missing";
}

if (file_exists(__DIR__ . '/database/schema.sql')) {
    $success[] = "✓ database/schema.sql exists (ready for import)";
    $filesize = filesize(__DIR__ . '/database/schema.sql');
    $success[] = "  Schema file size: " . number_format($filesize / 1024, 2) . " KB";
} else {
    $errors[] = "✗ database/schema.sql is missing (export your database)";
}

// Check 3: Security files
echo "\n[3] Checking security files...\n";
if (file_exists(__DIR__ . '/.htaccess')) {
    $success[] = "✓ .htaccess exists";
    
    $htaccessContent = file_get_contents(__DIR__ . '/.htaccess');
    if (strpos($htaccessContent, 'RewriteEngine On') !== false) {
        $success[] = "✓ .htaccess has rewrite rules";
    }
    if (strpos($htaccessContent, 'X-Frame-Options') !== false) {
        $success[] = "✓ .htaccess has security headers";
    }
} else {
    $errors[] = "✗ .htaccess is missing";
}

if (file_exists(__DIR__ . '/.gitignore')) {
    $success[] = "✓ .gitignore exists";
    
    $gitignoreContent = file_get_contents(__DIR__ . '/.gitignore');
    if (strpos($gitignoreContent, '.env') !== false) {
        $success[] = "✓ .gitignore excludes .env";
    } else {
        $errors[] = "✗ .gitignore does NOT exclude .env (security risk!)";
    }
} else {
    $warnings[] = "⚠ .gitignore is missing";
}

// Check 4: Dependencies
echo "\n[4] Checking dependencies...\n";
if (file_exists(__DIR__ . '/vendor/autoload.php')) {
    $success[] = "✓ Composer dependencies installed (vendor/)";
    
    if (file_exists(__DIR__ . '/vendor/phpmailer/phpmailer')) {
        $success[] = "✓ PHPMailer installed";
    } else {
        $warnings[] = "⚠ PHPMailer not found in vendor/";
    }
} else {
    $errors[] = "✗ Composer dependencies not installed (run: composer install)";
}

// Check 5: Error pages
echo "\n[5] Checking error pages...\n";
if (file_exists(__DIR__ . '/error/403.html')) {
    $success[] = "✓ 403 error page exists";
} else {
    $warnings[] = "⚠ 403 error page missing";
}

if (file_exists(__DIR__ . '/error/404.html')) {
    $success[] = "✓ 404 error page exists";
} else {
    $warnings[] = "⚠ 404 error page missing";
}

if (file_exists(__DIR__ . '/error/500.html')) {
    $success[] = "✓ 500 error page exists";
} else {
    $warnings[] = "⚠ 500 error page missing";
}

// Check 6: Documentation
echo "\n[6] Checking documentation...\n";
if (file_exists(__DIR__ . '/DEPLOYMENT.md')) {
    $success[] = "✓ DEPLOYMENT.md exists";
} else {
    $warnings[] = "⚠ DEPLOYMENT.md missing";
}

if (file_exists(__DIR__ . '/DEPLOYMENT_CHECKLIST.md')) {
    $success[] = "✓ DEPLOYMENT_CHECKLIST.md exists";
} else {
    $warnings[] = "⚠ DEPLOYMENT_CHECKLIST.md missing";
}

if (file_exists(__DIR__ . '/QUICK_START.md')) {
    $success[] = "✓ QUICK_START.md exists";
} else {
    $warnings[] = "⚠ QUICK_START.md missing";
}

// Check 7: Code issues
echo "\n[7] Checking for common issues...\n";
$phpFiles = array_merge(
    glob(__DIR__ . '/*.php') ?: [],
    glob(__DIR__ . '/*/*.php') ?: [],
    glob(__DIR__ . '/*/*/*.php') ?: []
);
$debugStatements = 0;
$filesWithDebug = [];

foreach ($phpFiles as $file) {
    if (strpos($file, 'vendor/') !== false) continue;
    
    $content = file_get_contents($file);
    
    // Check for debug statements
    if (preg_match('/\b(var_dump|print_r|var_export)\s*\(/i', $content)) {
        $debugStatements++;
        $filesWithDebug[] = basename($file);
    }
}

if ($debugStatements > 0) {
    $warnings[] = "⚠ Found $debugStatements debug statements in: " . implode(', ', array_slice($filesWithDebug, 0, 5));
} else {
    $success[] = "✓ No debug statements found";
}

// Check for localhost references
$localhostFiles = [];
foreach ($phpFiles as $file) {
    if (strpos($file, 'vendor/') !== false) continue;
    
    $content = file_get_contents($file);
    if (preg_match('/localhost|127\.0\.0\.1/i', $content)) {
        // Exclude .env file checks
        if (basename($file) !== 'check_deployment.php') {
            $localhostFiles[] = basename($file);
        }
    }
}

if (count($localhostFiles) > 0) {
    $warnings[] = "⚠ Localhost references found in: " . implode(', ', array_slice($localhostFiles, 0, 5));
    $warnings[] = "  Make sure these are in .env or commented out";
} else {
    $success[] = "✓ No hardcoded localhost references";
}

// Check 8: File permissions (on Unix systems)
if (PHP_OS_FAMILY !== 'Windows') {
    echo "\n[8] Checking file permissions...\n";
    
    $files = [
        '.env' => '0600',
        'config/config.php' => '0644',
        'database/database.php' => '0644',
    ];
    
    foreach ($files as $file => $recommended) {
        $path = __DIR__ . '/' . $file;
        if (file_exists($path)) {
            $perms = substr(sprintf('%o', fileperms($path)), -4);
            $success[] = "  $file: $perms";
        }
    }
}

// Print summary
echo "\n==============================================\n";
echo "  SUMMARY\n";
echo "==============================================\n\n";

if (count($success) > 0) {
    echo "SUCCESS (" . count($success) . "):\n";
    foreach ($success as $msg) {
        echo "  $msg\n";
    }
    echo "\n";
}

if (count($warnings) > 0) {
    echo "WARNINGS (" . count($warnings) . "):\n";
    foreach ($warnings as $msg) {
        echo "  $msg\n";
    }
    echo "\n";
}

if (count($errors) > 0) {
    echo "ERRORS (" . count($errors) . "):\n";
    foreach ($errors as $msg) {
        echo "  $msg\n";
    }
    echo "\n";
}

// Final verdict
echo "==============================================\n";
if (count($errors) === 0) {
    echo "✓ READY FOR DEPLOYMENT!\n";
    echo "\nNext steps:\n";
    echo "  1. Review DEPLOYMENT_CHECKLIST.md\n";
    echo "  2. Prepare production .env file\n";
    echo "  3. Upload files to InfinityFree\n";
    echo "  4. Import database\n";
    echo "  5. Test everything!\n";
} else {
    echo "✗ NOT READY - Please fix errors above\n";
}
echo "==============================================\n";

// Exit with appropriate code
exit(count($errors) > 0 ? 1 : 0);
