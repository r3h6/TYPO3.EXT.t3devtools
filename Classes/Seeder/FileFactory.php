<?php

namespace R3H6\T3devtools\Seeder;

use TYPO3\CMS\Core\Resource\File;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use R3H6\T3devtools\Generator\FakeFileGenerator;

class FileFactory
{
    protected const FILE_EXTENSIONS = [
        'docx',
        'gif',
        'jpg',
        'pdf',
        'png',
        'pptx',
        'xlsx',
    ];

    protected $extensions;

    protected $seeder;

    /**
     * @var \R3H6\T3devtools\Generator\FakeFileGenerator
     */
    protected $generator;

    public function __construct($seeder, $extensions = null)
    {
        $this->seeder = $seeder;
        $this->extensions = $extensions ?? self::FILE_EXTENSIONS;
        $this->generator = GeneralUtility::makeInstance(FakeFileGenerator::class);
    }

    public function create($countOrFileNames)
    {
        $fileNames = [];
        if (is_int($countOrFileNames)) {
            for ($i = 0; $i < $countOrFileNames; $i++) {
                $fileNames[] = uniqid('Fake') . '.' . $this->extensions[array_rand($this->extensions)];
            }
        }
        if (is_array($countOrFileNames)) {
            $fileNames += $countOrFileNames;
        }

        foreach ($fileNames as $sourceFile) {
            $files[] = $this->createFile($sourceFile);
        }

        return GeneralUtility::makeInstance(\R3H6\T3devtools\Seeder\File::class, $this->seeder, $files);
    }

    protected function createFile($sourceFile)
    {
        /** @var \TYPO3\CMS\Core\Resource\StorageRepository $storageRepository */
        $storageRepository = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(
            \TYPO3\CMS\Core\Resource\StorageRepository::class
        );

        /** @var $storage \TYPO3\CMS\Core\Resource\ResourceStorage */
        $storage = reset($storageRepository->findAll());

        try {
            $folder = $storage->getFolder($storage->getDefaultFolder()->getIdentifier() . 'import');
        } catch (\TYPO3\CMS\Core\Resource\Exception\FolderDoesNotExistException $exception) {
            $folder = $storage->createFolder($storage->getDefaultFolder()->getIdentifier() . 'import');
        }

        if (strpos($sourceFile, 'http') === 0) {
            $fileName = $storage->sanitizeFileName($sourceFile);
            if ($storage->hasFile($folder->getIdentifier() . $fileName)) {
                return $storage->getFile($folder->getIdentifier() . $fileName);
            }
            $tmpPath = GeneralUtility::tempnam('Mock');
            file_put_contents($tmpPath, file_get_contents($sourceFile));
        } else {
            list($fileName, $params) = GeneralUtility::trimExplode('?', $sourceFile);
            if ($storage->hasFile($folder->getIdentifier() . $fileName)) {
                return $storage->getFile($folder->getIdentifier() . $fileName);
            }
            $width = null;
            $height = null;
            if ($params) {
                list($width, $height) = GeneralUtility::trimExplode('x', (string)$params);
            }

            $tmpPath = GeneralUtility::tempnam('Mock', $fileName);
            /** \TYPO3\CMS\Core\Resource\File $file */
            $tmpFile = GeneralUtility::makeInstance(
                File::class,
                [
                    'name' => $fileName,
                    'extension' => pathinfo($fileName, PATHINFO_EXTENSION),
                    'width' => (int) ($width ?? 800),
                    'height' => (int) ($height ?? $width ?? 600),
                ],
                $storage
            );
            $this->generator->create($tmpFile, $tmpPath);
        }

        return $storage->addFile($tmpPath, $folder, $fileName);
    }
}