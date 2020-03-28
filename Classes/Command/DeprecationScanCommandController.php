<?php
declare(strict_types = 1);
namespace R3H6\T3devtools\Command;

use Helhum\Typo3Console\Mvc\Controller\CommandController;
use R3H6\T3devtools\Utility\ExtensionScannerUtility;
use R3H6\T3devtools\Utility\FileUtility;
use R3H6\T3devtools\Utility\IndicatorLevel;
use Symfony\Component\Finder\Finder;

/*
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */


/**
 * Command "deprecation:scan"
 */
class DeprecationScanCommandController extends CommandController
{
    /**
     * Scans code for deprecations.
     *
     * @param array $paths
     * @param string $level Minimum level for return error exit code
     * @param string $only Show only certain types of changes
     * @param boolean $first Exit immediatly on first error
     * @return void
     */
    public function executeCommand(array $paths, $level = 'strong', $only = 'all', $first = false)
    {
        $io = $this->output->getSymfonyConsoleOutput();

        $minIndicatorLevel = IndicatorLevel::cast($level);
        $exitOnError = $first;
        $fileNamePattern = '*.php';

        $matchers = ExtensionScannerUtility::getMatchers();

        $this->outputLine('Scanning files ('.$fileNamePattern.') in '.join(' ', $paths));

        $finder = new Finder();
        $files = $finder->files()->in($paths)->name($fileNamePattern)->sortByName();

        $errors = [];

        foreach ($files as $file) {
            $this->output('.');
            $matches = ExtensionScannerUtility::scanFile($file->getPathname(), $matchers);
            if (!empty($matches)){
                foreach ($matches as $match) {
                    $indicatorLevel = IndicatorLevel::cast($match['indicator']);
                    if ($indicatorLevel->gte($minIndicatorLevel) || $io->isVerbose()) {
                        $errors[] = [
                            'file' => $file,
                            'match' => $match,
                        ];

                        if ($exitOnError) {
                            break 2;
                        }
                    }
                }
            }
        }

        $this->outputLine();

        foreach ($errors as $error) {
            /** array $match */
            $match = $error['match'];

            /** \SplFileInfo $file */
            $file = $error['file'];

            $links = ExtensionScannerUtility::restFilesLinks($match['restFiles']);
            $line = (int) $match['line'];

            $relativePath = trim(str_replace($paths, '', $file->getPathname()), '/');

            $this->outputLine('<info>'.$relativePath.'</>');
            $this->outputLine($match['message'] . ' <comment>('.$match['indicator'].')</>');
            $this->outputLine('<comment>'.$line.'</> '.FileUtility::getLineFromFile($file->getPathname(), $line));
            if (!empty($links)) {
                foreach ($links as $link) {
                    $this->outputLine($link);
                }
            }
            $this->outputLine('');
        }

        if (!empty($errors)) {
            $this->quit(1);
        }
    }
}
