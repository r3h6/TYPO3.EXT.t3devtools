<?php
declare(strict_types = 1);
namespace R3H6\T3devtools\Utility;

use R3H6\T3devtools\Exception\MissingFileSignatureException;


class FileSignatures
{
    private static $signatures = [
        'ai'   => '25 50 44 46',
        'bmp'  => '42 4D',
        'doc'  => 'D0 CF 11 E0 A1 B1 1A E1',
        'docx' => '50 4B 03 04 14 00 06 00',
        'dot'  => 'D0 CF 11 E0 A1 B1 1A E1',
        'dotx' => '50 4B 03 04 14 00 06 00',
        'eps'  => '25 21 50 53 2D 41 64 6F',
        'exe'  => '4D 5A',
        'fdf'  => '25 50 44 46',
        'mp3'  => '49 44 33',
        'pdf'  => '25 50 44 46 2d',
        'pps'  => 'D0 CF 11 E0 A1 B1 1A E1',
        'ppt'  => 'D0 CF 11 E0 A1 B1 1A E1',
        'pptx' => '50 4B 03 04 14 00 06 00',
        'ps'   => '25 21 50 53',
        'psd'  => '38 42 50 53',
        'rtf'  => '7B 5C 72 74 66 31',
        'swf'  => '43 57 53',
        'tif'  => '49 20 49',
        'tiff' => '49 20 49',
        'xls'  => 'D0 CF 11 E0 A1 B1 1A E1',
        'xlsx' => '50 4B 03 04 14 00 06 00',
        'zip'  => '50 4B 03 04',
    ];

    public static function fromExtension($extension)
    {
        if (isset(static::$signatures[$extension])) {
            return hex2bin(str_replace(' ', '', static::$signatures[$extension]));
        }
        throw new MissingFileSignatureException($extension);
    }
}