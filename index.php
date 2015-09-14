<?php

$defaults = array(
    // how many spaces to use for indention, 0 will make it use real tabs
    'tabsize' => 4,

    // whitelist of IPs which don't need to be authenticated
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

/**
 * PHP Console
 *
 * A web-based php debug console
 *
 * Copyright (C) 2010, Jordi Boggiano
 * http://seld.be/ - j.boggiano@seld.be
 *
 * Licensed under the new BSD License
 * See the LICENSE file for details
 *
 * Source on Github http://github.com/Seldaek/php-console
 */
if (!in_array('*', $options['ip_whitelist'], true) &&
    !in_array($_SERVER['REMOTE_ADDR'], $options['ip_whitelist'], true)
) {
    header('HTTP/1.1 401 Access unauthorized');
    die('ERR/401 Go Away');
}

define('PHP_CONSOLE_VERSION', '1.4.0');
require 'lib/MelodyPlugin.php';
require 'vendor/autoload.php';

ini_set('log_errors', 0);
ini_set('display_errors', 1);
error_reporting(E_ALL | E_STRICT);

$debugOutput = '';

function runCode($__source_code, $__bootstrap_file)
{
    if ($__bootstrap_file) {
        require $__bootstrap_file;
    }
    eval($__source_code);
}

if (isset($_POST['code'])) {
	ini_set('html_errors', false);

    if (get_magic_quotes_gpc()) {
        $code = stripslashes($code);
    }

    $code = $_POST['code'];

//     // if there's only one line wrap it into a krumo() call
//     if (preg_match('#^(?!var_dump|echo|print|< )([^\r\n]+?);?\s*$#is', $code, $m) && trim($m[1])) {
//         $code = 'krumo('.$m[1].');';
//     }

    // replace '< foo' by krumo(foo)
//     $code = preg_replace('#^<\s+(.+?);?[\r\n]?$#m', 'krumo($1);', $code);

    // replace newlines in the entire code block by the new specified one
    // i.e. put #\r\n on the first line to emulate a file with windows line
    // endings if you're on a unix box
    if (preg_match('{#((?:\\\\[rn]){1,2})}', $code, $m)) {
        $newLineBreak = str_replace(array('\\n', '\\r'), array("\n", "\r"), $m[1]);
        $code = preg_replace('#(\r?\n|\r\n?)#', $newLineBreak, $code);
    }

    ob_start();
    $memBefore = memory_get_usage(true);
    $start = microtime(true);

    $melodyPlugin = new MelodyPlugin();
    if ($melodyPlugin->isMelodyScript($code)) {
        if ($melodyPlugin->isScriptingSupported()) {
            $melodyPlugin->runScript($code, $options['bootstrap']);
        } else {
            throw new Exception('php-console misses required dependencies to run melody scripts.');
        }
    } else {
        // Important: replace only line by line, so the generated source lines will map 1:1 to the initial user input!
        $code = preg_replace('{^\s*<\?(php)?\s*}i', '', $code);

        runCode($code, $options['bootstrap']);
    }

    // compare with peak, because regular memory could be free'd already
    $end = microtime(true);
    $memAfter = memory_get_peak_usage(true);
    $debugOutput .= ob_get_clean();

    if (isset($_GET['js'])) {
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
                            <!-- <a href="" class="reset">Reset</a> -->
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
