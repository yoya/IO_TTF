<?php

if (is_readable('vendor/autoload.php')) {
    require 'vendor/autoload.php';
} else {
    require_once 'IO/TTF.php';
}

$options = getopt("f:hvd");

if ((isset($options['f']) === false) || (($options['f'] !== "-") && is_readable($options['f']) === false)) {
    fprintf(STDERR, "Usage: php ttfdump.php -f <ttf_file> [-hvd]\n");
    fprintf(STDERR, "ex) php ttfdump.php -f test.ttf -h \n");
    exit(1);
}

$filename = $options['f'];
if ($filename === "-") {
    $filename = "php://stdin";
}
$ttfdata = file_get_contents($filename);

$opts = array();

if (isset($options['h'])) {
    $opts['hexdump'] = true;
}
if (isset($options['v'])) {
    $opts['verbose'] = true;
}
if (isset($options['d'])) {
    $opts['debug'] = true;
}

$ttf = new IO_TTF();
try {
    $ttf->parse($ttfdata, $opts);
} catch (Exception $e) {
    echo "ERROR: ttfdump: $filename:".PHP_EOL;
    echo $e->getMessage()." file:".$e->getFile()." line:".$e->getLine().PHP_EOL;
    echo $e->getTraceAsString().PHP_EOL;
    exit (1);
}


$ttf->dump($opts);
