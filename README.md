# IO_TTF
TrueType フォントのテーブルディレクトリを解析・ダンプします。

```sh
php sample/ttfdump.php -f input.ttf
```

## IO_Bit の利用箇所

テーブルディレクトリのオフセットや長さは `getUI32BE()` で読み取ります。
Composer を使わずに実行する場合は、各ライブラリを置いたディレクトリを PHP の `include_path` に含めてください。

[IO_Bit Wiki](https://github.com/yoya/IO_Bit/wiki)
