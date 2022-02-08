<?php

/*
  IO_TTF
  (c) 2022/02/08 yoya@awm.jp
  ref) https://docs.fileformat.com/font/ttf/
 */

if (is_readable('vendor/autoload.php')) {
    require 'vendor/autoload.php';
} else {
    require_once 'IO/Bit.php';
}

class IO_TTF {
    var $_table = [];
    function parse($ttfdata, $opts = array()) {
        $bit = new IO_Bit();
        $bit->input($ttfdata);
        $this->_scalerType = $bit->incrementOffset(4, 0); // unknown field
        $numTables = $bit->getUI16BE();
        $bit->incrementOffset(6, 0); // unknown field
        for ($i = 0; $i < $numTables; $i++) {
            $tag = $bit->getData(4);
            $checkSum = $bit->getUI32BE();
            $offset = $bit->getUI32BE();
            $length = $bit->getUI32BE();
            $this->_table []= [ "tag" => $tag, "checkSum" => $checkSum,
                                "offset" => $offset, "length" => $length ];
        }
    }

    function dump() {
        foreach ($this->_table as $tagInfo) {
            $tag = $tagInfo["tag"];
            $checkSum = $tagInfo["checkSum"];
            $offset = $tagInfo["offset"];
            $length = $tagInfo["length"];
            echo "tag:$tag checkSum:$checkSum offset:$offset length:$length";
            echo PHP_EOL;
        }
    }
}
