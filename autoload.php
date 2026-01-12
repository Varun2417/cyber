<?php
// Simple autoloader for PHPMailer
spl_autoload_register(function ($class) {
    // Only load PHPMailer classes
    $prefix = 'PHPMailer\\PHPMailer\\';
    $base_dir = __DIR__ . '/phpmailer/';

    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        // Not a PHPMailer class, skip
        return;
    }

    $relative_class = substr($class, $len);
    $file = $base_dir . $relative_class . '.php';

    if (file_exists($file)) {
        require $file;
    }
});
?>