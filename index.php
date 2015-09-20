<?php

/**
 * PHP Sandbox: https://github.com/MightyPork/php-sandbox
 */
 
ini_set('log_errors', false);
ini_set('display_errors', true);
ini_set('display_startup_errors', true);
ini_set('display_not_found_reason', true);
ini_set('display_exceptions', true);
ini_set('html_errors', false);

error_reporting(E_ALL | E_STRICT);

// --- Handle config ---

$defaults = array(
    // how many spaces to use for indention, 0 will make it use real tabs
    'tabsize' => 4,

    // whitelist of IPs
    // use '*' to allow any IP
    'ip_whitelist' => array('127.0.0.1', '::1'),

    // bootstrap file, if defined this file will be included before
    // the code entered by the user is evaluated. any variables and classes
    // defined here will be accessible by the eval'd code
    'bootstrap' => null,
);

if (file_exists(__DIR__.'/config.php')) {
    $options = include __DIR__.'/config.php';
    $options = array_merge($defaults, $options);
} else {
    $options = $defaults;
}


// --- IP Whitelist ---

if (!in_array('*', $options['ip_whitelist'], true) &&
    !in_array($_SERVER['REMOTE_ADDR'], $options['ip_whitelist'], true)
) {
	header("HTTP/1.0 401 Unauthorized");
    die('Access Denied');
}



$debugOutput = '';

if (isset($_POST['code'])) {
	ini_set('html_errors', false);

    $code = $_POST['code'];

    // --- Evaluate the code ---

    ob_start();
    $memBefore = memory_get_usage(true);
    $start = microtime(true);

    // Remove the < ?php mark
    // TODO remove also ? > if present
    $code = preg_replace('{^\s*<\?(php)?\s*}i', '', $code);

    /** Run code with bootstrap in separate scope */
    function runCode($__source_code, $__bootstrap_file)	{
	    if ($__bootstrap_file) {
	        require $__bootstrap_file;
	    }
	    eval($__source_code);
	}

    runCode($code, $options['bootstrap']);

    $end = microtime(true);
    $memAfter = memory_get_peak_usage(true);
    $debugOutput .= ob_get_clean();

    // ---------------------------

    if (isset($_GET['js'])) {

    	// --- Send response with metadata in headers ---
        header('Content-Type: text/plain');

        $memory = sprintf('%.3f', ($memAfter - $memBefore) / 1024.0 / 1024.0); // in MB
        $rendertime = sprintf('%.3f', (($end - $start) * 1000)); // in ms

        header('X-Memory-Usage: '. $memory);
        header('X-Rendertime: '. $rendertime);

        echo $debugOutput;

        die('#end-php-console-output#');
    }
}

?>
<!DOCTYPE html>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
        <meta http-equiv="X-UA-Compatible" content="IE=edge" />

        <title>PHP Sandbox</title>

        <link rel="stylesheet" type="text/css" href="styles.css" />
        <script src="jquery-1.9.1.min.js"></script>
        <script src="ace/ace.js"></script>
        <script src="ace/theme-monokai.js"></script>
        <script src="ace/mode-php.js"></script>
        <script src="ace/ext-language_tools.js"></script>
        <script src="php-console.js"></script>

        <script>
            $.console({
                tabsize: <?= json_encode($options['tabsize']) ?>
            });
        </script>
    </head>
    <body>
        <div class="console-wrapper">
            <div class="input-wrapper">
                <form method="POST" action="" id="mainform">
                    <div class="input">
                        <textarea class="editor" id="editor" name="code"><?=
                        	(isset($_POST['code']) ? htmlentities($_POST['code'], ENT_QUOTES, 'UTF-8') : "&lt;?php\n\n")
                        ?></textarea>
                    </div>
                        <div class="statusbar">
                            <span class="position">Line: 1, Column: 1</span>
                            <span class="runtime-info"></span>
                			<input type="submit" name="subm" value="Run!"/>
                        </div>
                </form>
            </div>
            <div class="output-wrapper">
                <div class="output"><?= htmlentities($debugOutput) ?></div>
            </div>
        </div>
    </body>
</html>
