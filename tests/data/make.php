<?php
/*
 * generate synthetic TrueType / OpenType test data for tests/run.php
 * usage: php tests/data/make.php
 *
 * Small fonts packed with pack() from the OpenType spec (offset table,
 * table directory sorted by tag, 4 byte aligned tables, checksums and
 * head.checkSumAdjustment). No real font data is used.
 */

$dir = dirname(__FILE__);

function checksum($data) {
    $data .= str_repeat("\0", (4 - strlen($data) % 4) % 4);
    $sum = 0;
    foreach (unpack('N*', $data) as $v) {
        $sum = ($sum + $v) & 0xffffffff;
    }
    return $sum;
}

// $tables: tag => body (directory order is the order given), $version: sfnt version (4 bytes)
function sfnt($version, $tables) {
    $n = count($tables);
    $es = $n ? (int) floor(log($n, 2)) : 0;
    $sr = $n ? (1 << $es) * 16 : 0;
    $head = $version.pack('nnnn', $n, $sr, $es, $n * 16 - $sr);
    $offset = 12 + 16 * $n;
    $dirent = '';
    $body = '';
    foreach ($tables as $tag => $data) {
        $dirent .= $tag.pack('NNN', checksum($data), $offset, strlen($data));
        $pad = str_repeat("\0", (4 - strlen($data) % 4) % 4);
        $body .= $data.$pad;
        $offset += strlen($data.$pad);
    }
    $font = $head.$dirent.$body;
    if (isset($tables['head'])) { // checkSumAdjustment (head offset 8)
        $pos = strpos($font, 'head');
        $hoff = unpack('N', substr($font, $pos + 8, 4))[1];
        $adj = (0xb1b0afba - checksum($font)) & 0xffffffff;
        $font = substr_replace($font, pack('N', $adj), $hoff + 8, 4);
    }
    return $font;
}

$head = pack('NNNNnn', 0x00010000, 0x00010000, 0, 0x5f0f3cf5, 0x000b, 1000)
      .pack('NNNN', 0, 0xdf0f2a00, 0, 0xdf0f2a00)   // created, modified (2018-08-16)
      .pack('nnnnnnnnn', 0, 0, 500, 700, 0, 8, 2, 0, 0);
$hhea = pack('Nnnnnnnnnnnnnnnnn', 0x00010000, 800, -200 & 0xffff, 0, 500, 0, 0, 500, 1, 0, 0,
             0, 0, 0, 0, 0, 1);
$maxp = pack('Nnnnnnnnnnnnnnn', 0x00010000, 1, 0, 0, 0, 0, 2, 0, 0, 0, 0, 0, 0, 0, 0);
$hmtx = pack('nn', 500, 0);
$loca = pack('nn', 0, 0);                  // short offsets, glyph 0 is empty
$cmap = pack('nnnnN', 0, 1, 3, 1, 12)      // (3,1) format 4, one 0xFFFF segment
      .pack('nnnnnnnnnnnn', 4, 24, 0, 2, 2, 0, 0, 0xffff, 0, 0xffff, 1, 0);
$name = pack('nnn', 0, 1, 18).pack('nnnnnn', 3, 1, 0x409, 1, 8, 0)
      ."\0T\0e\0s\0t";             // "Test" in UTF-16BE
$post = pack('NNnnNNNNN', 0x00030000, 0, -100 & 0xffff, 50, 0, 0, 0, 0, 0);
$cvt  = pack('nnn', 0, 100, -100 & 0xffff); // 6 bytes: needs padding

$minimal = array('cmap' => $cmap, 'cvt ' => $cvt, 'glyf' => '', 'head' => $head,
                 'hhea' => $hhea, 'hmtx' => $hmtx, 'loca' => $loca, 'maxp' => $maxp,
                 'name' => $name, 'post' => $post);
$minimalTTF = sfnt("\x00\x01\x00\x00", $minimal);

$files = array(
    'minimal.ttf'  => $minimalTTF,
    // CFF based OpenType (the CFF body is only a header here)
    'otto.otf'     => sfnt('OTTO', array('CFF ' => "\x01\x00\x04\x01", 'head' => $head,
                                         'maxp' => pack('Nn', 0x00005000, 1))),
    // Apple 'true' version, directory not sorted by tag
    'apple.ttf'    => sfnt('true', array('post' => $post, 'head' => $head)),
    'no_tables.ttf' => sfnt("\x00\x01\x00\x00", array()),
    'truncated_dir.ttf'    => substr($minimalTTF, 0, 12 + 16 * 3 + 5),
    'truncated_header.ttf' => substr($minimalTTF, 0, 5),
    'empty.ttf'    => '',
);

foreach ($files as $name => $data) {
    file_put_contents("$dir/$name", $data);
}
