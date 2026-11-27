<?php
/*
 * regression tests (no dependency)
 * usage: php tests/run.php
 *   IO_Bit is loaded from vendor/ (composer install) or include_path.
 */

$root = dirname(dirname(__FILE__));
if (is_readable($root.'/vendor/autoload.php')) {
    require_once $root.'/vendor/autoload.php';
}
set_include_path($root.PATH_SEPARATOR.get_include_path());

$failed = 0;
$passed = 0;

function check($name, $expect, $actual) {
    global $failed, $passed;
    if ($expect === $actual) {
        $passed++;
        return;
    }
    $failed++;
    echo "FAIL: $name".PHP_EOL;
    echo "  expect: ".var_export($expect, true).PHP_EOL;
    echo "  actual: ".var_export($actual, true).PHP_EOL;
}

// synthetic TTF: offset table and table directory (table bodies are not read)
function ttf($tables) {
    $data = pack('Nn', 0x00010000, count($tables)).pack('nnn', 0, 0, 0);
    foreach ($tables as $t) {
        $data .= $t[0].pack('NNN', $t[1], $t[2], $t[3]);
    }
    return $data;
}

// run php in a child process (for warnings, exit status and STDOUT/STDERR)
function runPHP($args, $stdin, $include_path = null, $cwd = null) {
    global $root;
    $cmd = array(PHP_BINARY, '-d', 'display_errors=stderr', '-d', 'log_errors=0', '-d', 'error_reporting='.E_ALL,
                 '-d', 'include_path='.(is_null($include_path) ? get_include_path() : $include_path));
    $cmd = implode(' ', array_map('escapeshellarg', array_merge($cmd, $args)));
    $proc = proc_open($cmd, array(array('pipe', 'r'), array('pipe', 'w'), array('pipe', 'w')), $pipes, is_null($cwd) ? $root : $cwd);
    fwrite($pipes[0], $stdin);
    fclose($pipes[0]);
    $stdout = stream_get_contents($pipes[1]);
    $stderr = stream_get_contents($pipes[2]);
    fclose($pipes[1]);
    fclose($pipes[2]);
    $status = proc_close($proc);
    return array($status, $stdout, $stderr);
}

// parse and dump in a child process
function dumpTTF($data) {
    return runPHP(array('-r', 'require "IO/TTF.php"; $t = new IO_TTF(); $t->parse(stream_get_contents(STDIN)); $t->dump();'), $data);
}

$basic = ttf(array(array('OS/2', 0x12345678, 44, 96), array('cmap', 0xFFFFFFFF, 140, 3532)));

/*
 * parses and dumps without warnings (_scalerType was a dynamic property, deprecated in PHP 8.2)
 */
list($status, $stdout, $stderr) = dumpTTF($basic);
check('dump: exit status', 0, $status);
check('dump: no warnings', '', $stderr);
check('dump: output', "tag:OS/2 checkSum:305419896 offset:44 length:96\ntag:cmap checkSum:4294967295 offset:140 length:3532\n", $stdout);

echo "passed: $passed, failed: $failed".PHP_EOL;
exit($failed ? 1 : 0);
