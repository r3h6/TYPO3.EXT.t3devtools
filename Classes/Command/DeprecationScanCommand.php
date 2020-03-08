<?php
declare(strict_types = 1);
namespace R3H6\T3devtools\Command;

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

use R3H6\T3devtools\Utility\ExtensionScannerUtility;
use R3H6\T3devtools\Utility\FileUtility;
use R3H6\T3devtools\Utility\IndicatorLevel;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Command "deprecation:scan"
 */
class DeprecationScanCommand extends AbstractCommand
{
    protected function configure()
    {
        $this
            ->setDescription('Scans files for code deprecations')
            ->addOption('only', 'o', InputOption::VALUE_OPTIONAL, 'Show only certain types of changes', 'all')
            ->addOption('level', 'l', InputOption::VALUE_OPTIONAL, 'Minimum level for return error exit code', 'strong')
            ->addOption('first', null, InputOption::VALUE_NONE, 'Exit immediatly on first error')
            ->addArgument('paths', InputArgument::IS_ARRAY | InputArgument::REQUIRED, 'Path for scanning files')
        ;
    }


    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->io->title('Scanning for deprecations');

        $paths = $input->getArgument('paths');
        $minIndicatorLevel = IndicatorLevel::cast($input->getOption('level'));
        $exitOnError = $input->getOption('first');
        $fileNamePattern = '*.php';

        $hasError = false;

        $matchers = ExtensionScannerUtility::getMatchers();
        $finder = new Finder();
        $files = $finder->files()->in($paths)->name($fileNamePattern)->sortByName();
        foreach ($files as $file) {
            $absoluteFilePath = $file->getPathname();
            $relativePath = trim(str_replace($paths, '', $absoluteFilePath), '/');

            $matches = ExtensionScannerUtility::scanFile($absoluteFilePath, $matchers);
            if (!empty($matches)){
                foreach ($matches as $match) {

                    $indicatorLevel = IndicatorLevel::cast($match['indicator']);
                    $links = ExtensionScannerUtility::restFilesLinks($match['restFiles']);
                    $line = (int) $match['line'];

                    if ($indicatorLevel->gte($minIndicatorLevel) || $this->io->isVerbose()) {
                        $this->io->writeln('<info>'.$relativePath.'</>');
                        $this->io->writeln($match['message'] . ' <comment>('.$match['indicator'].')</>');
                        $this->io->writeln('<comment>'.$line.'</> '.FileUtility::getLineFromFile($absoluteFilePath, $line));
                        $this->io->writeln($links);
                        $this->io->writeln('');
                    }

                    if ($indicatorLevel->gte($minIndicatorLevel)) {
                        $hasError = true;
                        if ($exitOnError) {
                            break 2;
                        }
                    }
                }
            }
        }
        return $hasError ? 1: 0;
    }


}