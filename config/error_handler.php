<?php
/**
 * Error Handler Configuration
 * Centralized error handling for production environment
 */

// Load configuration
require_once __DIR__ . '/config.php';
Config::load();

// Set error reporting based on environment
if (Config::isProduction()) {
    // Production: Log errors, don't display
    error_reporting(E_ALL);
    ini_set('display_errors', '0');
    ini_set('display_startup_errors', '0');
    ini_set('log_errors', '1');
    
    // Set error log file
    $logDir = __DIR__ . '/../logs';
    if (!is_dir($logDir)) {
        @mkdir($logDir, 0755, true);
    }
    ini_set('error_log', $logDir . '/error.log');
} else {
    // Development: Display errors
    error_reporting(E_ALL);
    ini_set('display_errors', '1');
    ini_set('display_startup_errors', '1');
}

/**
 * Custom error handler
 */
function customErrorHandler($errno, $errstr, $errfile, $errline) {
    if (!(error_reporting() & $errno)) {
        return false;
    }

    $errorTypes = [
        E_ERROR => 'ERROR',
        E_WARNING => 'WARNING',
        E_PARSE => 'PARSE ERROR',
        E_NOTICE => 'NOTICE',
        E_CORE_ERROR => 'CORE ERROR',
        E_CORE_WARNING => 'CORE WARNING',
        E_COMPILE_ERROR => 'COMPILE ERROR',
        E_COMPILE_WARNING => 'COMPILE WARNING',
        E_USER_ERROR => 'USER ERROR',
        E_USER_WARNING => 'USER WARNING',
        E_USER_NOTICE => 'USER NOTICE',
        E_STRICT => 'STRICT NOTICE',
        E_RECOVERABLE_ERROR => 'RECOVERABLE ERROR',
        E_DEPRECATED => 'DEPRECATED',
        E_USER_DEPRECATED => 'USER DEPRECATED'
    ];

    $errorType = isset($errorTypes[$errno]) ? $errorTypes[$errno] : 'UNKNOWN ERROR';
    $message = sprintf(
        "[%s] %s: %s in %s on line %d",
        date('Y-m-d H:i:s'),
        $errorType,
        $errstr,
        $errfile,
        $errline
    );

    error_log($message);

    // In production, show user-friendly message
    if (Config::isProduction()) {
        if ($errno === E_ERROR || $errno === E_USER_ERROR) {
            http_response_code(500);
            include __DIR__ . '/../error/500.php';
            exit();
        }
    }

    return true;
}

/**
 * Custom exception handler
 */
function customExceptionHandler($exception) {
    $message = sprintf(
        "[%s] Uncaught Exception: %s in %s on line %d\nStack trace:\n%s",
        date('Y-m-d H:i:s'),
        $exception->getMessage(),
        $exception->getFile(),
        $exception->getLine(),
        $exception->getTraceAsString()
    );

    error_log($message);

    // In production, show user-friendly message
    if (Config::isProduction()) {
        http_response_code(500);
        if (file_exists(__DIR__ . '/../error/500.php')) {
            include __DIR__ . '/../error/500.php';
        } else {
            echo '<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Error</title>
    <style>
        body { font-family: Arial, sans-serif; text-align: center; padding: 50px; background: #f5f5f5; }
        .error-container { background: white; padding: 40px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); max-width: 500px; margin: 0 auto; }
        h1 { color: #e74c3c; }
        p { color: #666; }
    </style>
</head>
<body>
    <div class="error-container">
        <h1>Oops! Something went wrong</h1>
        <p>We\'re sorry, but an error occurred while processing your request.</p>
        <p>Please try again later or contact the administrator if the problem persists.</p>
        <a href="/" style="color: #3498db; text-decoration: none;">← Go back to homepage</a>
    </div>
</body>
</html>';
        }
        exit();
    } else {
        // In development, show detailed error
        echo '<pre>' . htmlspecialchars($message) . '</pre>';
    }
}

/**
 * Shutdown function to catch fatal errors
 */
function customShutdownHandler() {
    $error = error_get_last();
    if ($error !== null && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
        customErrorHandler($error['type'], $error['message'], $error['file'], $error['line']);
    }
}

// Set custom handlers
set_error_handler('customErrorHandler');
set_exception_handler('customExceptionHandler');
register_shutdown_function('customShutdownHandler');
