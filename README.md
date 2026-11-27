IO_TTF
======

English version: [README.en.md](README.en.md)

TrueType フォント (.ttf) のテーブルディレクトリを解析・ダンプする PHP ライブラリです。IO_Bit が必要です。
ファイル先頭のオフセットテーブルとテーブルディレクトリを配列に読み込み (parse)、ダンプし (dump) ます。
各テーブルの中身の解釈や、書き出し (build) はありません。初期の試作です。
利用に制限はかけません。コピーも改変もご自由にどうぞ。MIT ライセンスにしました。
自分の環境だと動かないとか、機能が欲しいとかいった要望があれば連絡下さい。

# License

MIT License

# Install

```
% composer require yoya/io_ttf
```

サンプルは、Composer で入れたプロジェクトのトップで動かして下さい (IO_Bit をカレントディレクトリの `vendor/autoload.php` から読みます)。

# Usage

- ttfdump.php (テーブルディレクトリのダンプ)

```
% php vendor/yoya/io_ttf/sample/ttfdump.php -f input.ttf
tag:OS/2 checkSum:2317171838 offset:328 length:96
tag:cmap checkSum:254480870 offset:424 length:1180
tag:glyf checkSum:1547578001 offset:1604 length:20480
tag:head checkSum:4053971908 offset:188 length:54
(omit)
tag:post checkSum:1567522688 offset:25124 length:32
```

`-f -` で標準入力から読みます。

- PHP から使う例 (テーブルの名前と大きさを並べる)

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

# 設計概要

| クラス | 役割 |
|---|---|
| `IO_TTF` | TTF のテーブルディレクトリ。`parse()` と `dump()` |
| `IO_Bit` (別パッケージ) | バイト・ビット単位の読み込み |

- `parse()` はオフセットテーブル (12 バイト) からテーブル数を読み、テーブルディレクトリ (16 バイト × テーブル数) を `_table` プロパティに入れます。
  - `_table` の 1 つずつが `tag` (4 文字), `checkSum`, `offset`, `length` を持ちます。並びはファイルの中の順です。
  - オフセットテーブルのテーブル数以外のフィールド (sfnt version, searchRange, entrySelector, rangeShift) は読み飛ばします。
- `dump()` は `_table` を 1 行ずつ `tag:... checkSum:... offset:... length:...` の形で出します (数値は 10 進)。
- データが途中で切れているときは、`parse()` が IO_Bit_Exception を投げます。ttfdump.php はエラーの内容を標準出力に出し、終了コード 1 で終わります。

## IO_Bit の利用箇所

テーブルディレクトリのオフセットや長さは `getUI32BE()` で読み取ります。
Composer を使わずに実行する場合は、各ライブラリを置いたディレクトリを PHP の `include_path` に含めてください。

[IO_Bit Wiki](https://github.com/yoya/IO_Bit/wiki)

# 既知の制限

- テーブルディレクトリを読むだけです。各テーブルの中身は解釈せず、build もありません。
- `checkSum` は読むだけで、検証はしません。
- sfnt version は確かめません。TrueType Collection (.ttc, `ttcf`) の形式には対応していません。
- `_scalerType` プロパティには sfnt version の値ではなく `true` が入ります。
- ttfdump.php の `-h`, `-v`, `-d` オプションは受け付けますが、今は出力に影響しません。
- 同じオブジェクトで `parse()` を 2 回呼ぶと、`_table` に追記されます。

# Test

```
% php -d include_path=.:../IO_Bit tests/run.php
```

IO_Bit は `vendor/` (composer install した場合) か include_path から読みます。

# Reference

- https://docs.fileformat.com/font/ttf/

# More detail

- https://github.com/yoya/IO_TTF/wiki
