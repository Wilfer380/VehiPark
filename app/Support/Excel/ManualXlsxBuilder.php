<?php

namespace App\Support\Excel;

final class ManualXlsxBuilder
{
    public static function build(array $files): string
    {
        $data = '';
        $centralDirectory = '';
        $offset = 0;

        foreach ($files as $name => $content) {
            $name = str_replace('\\', '/', $name);
            $content = (string) $content;
            $nameLength = strlen($name);
            $contentLength = strlen($content);
            $crc = crc32($content);

            if ($crc < 0) {
                $crc += 4294967296;
            }

            $localHeader =
                self::pack32(0x04034b50) .
                self::pack16(20) .
                self::pack16(0) .
                self::pack16(0) .
                self::pack16(0) .
                self::pack16(0) .
                self::pack32($crc) .
                self::pack32($contentLength) .
                self::pack32($contentLength) .
                self::pack16($nameLength) .
                self::pack16(0) .
                $name .
                $content;

            $data .= $localHeader;

            $centralDirectory .=
                self::pack32(0x02014b50) .
                self::pack16(20) .
                self::pack16(20) .
                self::pack16(0) .
                self::pack16(0) .
                self::pack16(0) .
                self::pack16(0) .
                self::pack32($crc) .
                self::pack32($contentLength) .
                self::pack32($contentLength) .
                self::pack16($nameLength) .
                self::pack16(0) .
                self::pack16(0) .
                self::pack16(0) .
                self::pack16(0) .
                self::pack32(0) .
                self::pack32($offset) .
                $name;

            $offset += strlen($localHeader);
        }

        $centralDirectoryOffset = strlen($data);
        $data .= $centralDirectory;
        $data .=
            self::pack32(0x06054b50) .
            self::pack16(0) .
            self::pack16(0) .
            self::pack16(count($files)) .
            self::pack16(count($files)) .
            self::pack32(strlen($centralDirectory)) .
            self::pack32($centralDirectoryOffset) .
            self::pack16(0);

        return $data;
    }

    public static function row(int $rowNumber, array $values, ?int $styleId = null): string
    {
        $xml = '<row r="' . $rowNumber . '" spans="1:' . count($values) . '">';

        foreach ($values as $index => $value) {
            $cellRef = self::column($index + 1) . $rowNumber;
            $styleAttribute = $styleId === null ? '' : ' s="' . $styleId . '"';
            $xml .= '<c r="' . $cellRef . '" t="inlineStr"' . $styleAttribute . '><is><t xml:space="preserve">' . self::escape((string) $value) . '</t></is></c>';
        }

        return $xml . '</row>';
    }

    public static function column(int $index): string
    {
        $column = '';

        while ($index > 0) {
            $index--;
            $column = chr(65 + ($index % 26)) . $column;
            $index = intdiv($index, 26);
        }

        return $column;
    }

    public static function escape(string $value): string
    {
        return htmlspecialchars($value, ENT_XML1 | ENT_QUOTES, 'UTF-8');
    }

    private static function pack16(int $value): string
    {
        return pack('v', $value & 0xffff);
    }

    private static function pack32(int $value): string
    {
        return pack('V', $value & 0xffffffff);
    }
}
