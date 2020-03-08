<?php
declare(strict_types = 1);
namespace R3H6\T3devtools\Generator;

use TYPO3\CMS\Core\Resource\File;
use TYPO3\CMS\Core\SingletonInterface;

interface FakeFileGeneratorInterface extends SingletonInterface
{
    public function create(File $file, string $filePath): void;
}