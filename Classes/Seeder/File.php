<?php

namespace R3H6\T3devtools\Seeder;

use TYPO3\CMS\Core\Utility\GeneralUtility;
use R3H6\T3devtools\Generator\FakeFileGenerator;
use TYPO3\CMS\Core\Utility\MathUtility;

class File
{
    protected $files;

    protected $uploadDir;

    protected $faker;

    protected $generator;

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

    public function __construct($uploadDir)
    {
        $this->uploadDir = trim($uploadDir, '/');
        $this->files = new \ArrayObject();

        $this->extensions = self::FILE_EXTENSIONS;
        $this->generator = GeneralUtility::makeInstance(FakeFileGenerator::class);
    }

    public function each($callback)
    {
        foreach ($this->files as $file) {
            call_user_func($callback, $file, $this->faker);
        }
        return $this;
    }

    public function add(\TYPO3\CMS\Core\Resource\File $file): self
    {
        $this->files[$file->getUid()] = $file;
        return $this;
    }

    public function csv()
    {
        $ids = [];
        foreach ($this->files as $file) {
            $ids[] = $file->getUid();
        }
        return join(',', $ids);
    }

    public function random($amount): self
    {
        $newSet = new self($this->uploadDir);
        $files = $this->files->getArrayCopy();
        $keys = (array) array_rand($files, $amount);
        foreach ($keys as $key) {
            $newSet->add($files[$key]);
        }

        return $newSet;
    }

    public function one(): self
    {
        return $this->random(1);
    }

    public function getFiles()
    {
        return $this->files;
    }


    public function create($countOrFileMap)
    {
        $fileMap = [];
        if (is_int($countOrFileMap)) {
            for ($i = 0; $i < $countOrFileMap; $i++) {
                $targetName = uniqid('Mock') . '.' . $this->extensions[array_rand($this->extensions)];
                $fileMap[$targetName] = 'local';
            }
        }
        if (is_array($countOrFileMap)) {
            $fileMap += $countOrFileMap;
        }

        foreach ($fileMap as $targetName => $processorConfig) {
            $this->add($this->createFile($targetName, $processorConfig));
        }

        return $this;
    }

    protected function createFile($targetName, $processorConfig)
    {
        /** @var \TYPO3\CMS\Core\Resource\StorageRepository $storageRepository */
        $storageRepository = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(
            \TYPO3\CMS\Core\Resource\StorageRepository::class
        );

        /** @var $storage \TYPO3\CMS\Core\Resource\ResourceStorage */
        $storage = reset($storageRepository->findAll());
        list($processorType, $size, $extension) = GeneralUtility::trimExplode(';', $processorConfig);

        if (!$extension) {
            $extension = $this->extensions[array_rand($this->extensions)];
        }

        if (MathUtility::canBeInterpretedAsInteger($targetName)) {
            $targetName = $storage->sanitizeFileName($processorConfig) . '.' . $extension;
        }

        try {
            $folder = $storage->getFolder($storage->getDefaultFolder()->getIdentifier() . $this->uploadDir);
        } catch (\TYPO3\CMS\Core\Resource\Exception\FolderDoesNotExistException $exception) {
            $folder = $storage->createFolder($storage->getDefaultFolder()->getIdentifier() . $this->uploadDir);
        }

        if ($storage->hasFile($folder->getIdentifier() . $targetName)) {
            return $storage->getFile($folder->getIdentifier() . $targetName);
        }

        if (strpos($processorType, 'http') === 0) {
            $tmpPath = GeneralUtility::tempnam('Mock');
            file_put_contents($tmpPath, file_get_contents($processorType));
        } else {

            $width = null;
            $height = null;
            if ($size) {
                list($width, $height) = GeneralUtility::trimExplode('x', (string) $size);
            }

            $tmpPath = GeneralUtility::tempnam('Mock', $targetName);
            /** \TYPO3\CMS\Core\Resource\File $file */
            $tmpFile = GeneralUtility::makeInstance(
                \TYPO3\CMS\Core\Resource\File::class,
                [
                    'name' => $targetName,
                    'extension' => pathinfo($targetName, PATHINFO_EXTENSION),
                    'width' => (int) ($width ?? 800),
                    'height' => (int) ($height ?? $width ?? 600),
                ],
                $storage
            );
            $this->generator->create($tmpFile, $tmpPath);
        }

        return $storage->addFile($tmpPath, $folder, $targetName);
    }
}