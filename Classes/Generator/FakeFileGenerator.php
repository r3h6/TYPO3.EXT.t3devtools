<?php
declare(strict_types = 1);
namespace R3H6\T3devtools\Generator;

use R3H6\T3devtools\Exception\MissingFileSignatureException;
use R3H6\T3devtools\Utility\FileSignatures;
use TYPO3\CMS\Core\Resource\File;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class FakeFileGenerator implements FakeFileGeneratorInterface
{
    static private $generators = [
        'gif' => FakeImageGenerator::class,
        'png' => FakeImageGenerator::class,
        'jpg' => FakeImageGenerator::class,
        'jpeg' => FakeImageGenerator::class,
        'tif' => FakeImageGenerator::class,
        'tiff' => FakeImageGenerator::class,
    ];

    public function create(File $file, string $filePath): void
    {
        $fileExtension = $file->getExtension();

        $dir = dirname($filePath);
        GeneralUtility::mkdir_deep($dir);

        if (isset(static::$generators[$fileExtension])) {
            $generator = GeneralUtility::makeInstance(static::$generators[$fileExtension]);
            $generator->create($file, $filePath);
        } else {
            try {
                $content = FileSignatures::fromExtension($fileExtension);
            } catch(MissingFileSignatureException $exception) {
                $content = '';
            }
            if ($fileExtension === 'youtube') {
                $content = 'ScMzIvxBSi4';
            }
            if ($fileExtension === 'vimeo') {
                $content = '338848750';
            }
            file_put_contents($filePath, $content);
            GeneralUtility::fixPermissions($filePath);
        }
    }
}