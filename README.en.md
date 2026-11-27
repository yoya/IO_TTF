IO_TTF
======

Japanese version: [README.md](README.md)

A PHP library for parsing and dumping the table directory of a TrueType font (.ttf). It requires IO_Bit.
It reads the offset table and the table directory at the top of the file into an array (parse), and dumps them (dump).
It does not interpret the contents of each table, and there is no writer (build). It is an early prototype.
Not limited in use, free to copy and modify under the MIT license.
Please contact me if it does not work in your environment or if you want new features.

# License

MIT License

# Install

```
% composer require yoya/io_ttf
```

Run the sample at the top of the project where you installed it with Composer (IO_Bit is loaded from `vendor/autoload.php` in the current directory).

# Usage

- ttfdump.php (dumps the table directory)

```
% php vendor/yoya/io_ttf/sample/ttfdump.php -f input.ttf
tag:OS/2 checkSum:2317171838 offset:328 length:96
tag:cmap checkSum:254480870 offset:424 length:1180
tag:glyf checkSum:1547578001 offset:1604 length:20480
tag:head checkSum:4053971908 offset:188 length:54
(omit)
tag:post checkSum:1567522688 offset:25124 length:32
```

`-f -` reads from standard input.

- Using it from PHP (lists the name and size of each table)

```php
<?php
require 'vendor/autoload.php';

$ttf = new IO_TTF();
$ttf->parse(file_get_contents('input.ttf'));
foreach ($ttf->_table as $table) {
    echo $table['tag']." ".$table['length'].PHP_EOL;
}
```

```
% php tables.php
OS/2 96
cmap 1180
glyf 20480
(omit)
```

# Design

| Class | Role |
|---|---|
| `IO_TTF` | The TTF table directory. `parse()` and `dump()` |
| `IO_Bit` (separate package) | Byte and bit level reading |

- `parse()` reads the number of tables from the offset table (12 bytes) and stores the table directory (16 bytes per table) in the `_table` property.
  - Each entry of `_table` has `tag` (4 characters), `checkSum`, `offset` and `length`, in file order.
  - The other fields of the offset table (sfnt version, searchRange, entrySelector, rangeShift) are skipped.
- `dump()` prints one line per `_table` entry in the form `tag:... checkSum:... offset:... length:...` (numbers in decimal).
- Truncated data makes `parse()` throw an IO_Bit_Exception. ttfdump.php prints the error to standard output and exits with status 1.

## Where IO_Bit is used

The offsets and lengths in the table directory are read with `getUI32BE()`.
When running without Composer, add the directories where you put each library to PHP's `include_path`.

[IO_Bit Wiki](https://github.com/yoya/IO_Bit/wiki)

# Known limitations

- It only reads the table directory. The contents of each table are not interpreted, and there is no build.
- `checkSum` is read but not verified.
- The sfnt version is not checked. The TrueType Collection (.ttc, `ttcf`) format is not supported.
- The `_scalerType` property holds `true`, not the sfnt version value.
- ttfdump.php accepts the `-h`, `-v` and `-d` options, but they do not change the output for now.
- Calling `parse()` twice on the same object appends to `_table`.

# Test

```
% php -d include_path=.:../IO_Bit tests/run.php
```

IO_Bit is loaded from `vendor/` (after composer install) or include_path.

# Reference

- https://docs.fileformat.com/font/ttf/

# More detail

- https://github.com/yoya/IO_TTF/wiki
