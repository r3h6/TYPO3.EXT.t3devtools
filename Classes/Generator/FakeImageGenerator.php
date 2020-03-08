<?php
declare(strict_types = 1);
namespace R3H6\T3devtools\Generator;

use TYPO3\CMS\Core\Resource\File;
use TYPO3\CMS\Core\Utility\CommandUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;


class FakeImageGenerator implements FakeFileGeneratorInterface
{
    protected const FONT_VERA = 'EXT:install/Resources/Private/Font/vera.ttf';

    /**
     * @var string
     */
    protected $font;

    public function __construct($font = self::FONT_VERA)
    {
        $this->font = GeneralUtility::getFileAbsFileName($font);
    }

    public function create(File $file, string $filePath): void
    {
        $width = $file->getProperty('width');
        $height = $file->getProperty('height');
        if ($width && $height) {
            $params = '-size ' . $width . 'x' . $height
                . ' -background lightgrey'
                . ' -fill darkgrey'
                . ' -pointsize 24'
                . ' -gravity center'
                // . ' -draw "line 0,0 '.$width.','.$height.' line 0,'.$height.' '.$width.',0"'
                . ' -font ' . escapeshellarg($this->font)
                . ' label:\'' . basename($filePath) .'\\n'. $width . 'x' . $height . 'px\'';
            $cmd = CommandUtility::imageMagickCommand(
                'convert',
                $params . ' ' . escapeshellarg($filePath)
            );
            CommandUtility::exec($cmd);
            GeneralUtility::fixPermissions($filePath);
        }
    }
}
