<?php
declare(strict_types = 1);
namespace R3H6\T3devtools\Command;

use Symfony\Component\Finder\Finder;
use R3H6\T3devtools\Utility\FileUtility;
use R3H6\T3devtools\Utility\IndicatorLevel;
use R3H6\T3devtools\Command\AbstractCommand;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use R3H6\T3devtools\Utility\ExtensionScannerUtility;
use Symfony\Component\Console\Output\OutputInterface;

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
class DeprecationScanCommandController extends AbstractCommand
{

    protected function configure()
    {
        $this
            ->setDescription('Scans code for deprecations.')
            ->addArgument('path', InputArgument::REQUIRED | InputArgument::IS_ARRAY, 'Directory path to scan')
            ->addOption('level', null, InputOption::VALUE_OPTIONAL, 'Minimum level for return error exit code', 'strong')
            ->addOption('only', null, InputOption::VALUE_OPTIONAL, 'Show only certain types of changes',  'all')
            ->addOption('first', null, InputOption::VALUE_OPTIONAL, 'Exit immediatly on first error', false)
        ;
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $paths = (array) $input->getArgument('path');
        $level = $input->getOption('level');
        $only = $input->getOption('only');
        $first = $input->getOption('first');

        $minIndicatorLevel = IndicatorLevel::cast($level);
        $exitOnError = $first;
        $fileNamePattern = '*.php';

        $matchers = ExtensionScannerUtility::getMatchers();

        $this->io->writeln('Scanning files ('.$fileNamePattern.') in '.join(' ', $paths));

        $finder = new Finder();
        $files = $finder->files()->in($paths)->name($fileNamePattern)->sortByName();

        $errors = [];

        foreach ($files as $file) {
            $this->io->write('.');
            $matches = ExtensionScannerUtility::scanFile($file->getPathname(), $matchers);
            if (!empty($matches)){
                foreach ($matches as $match) {
                    $indicatorLevel = IndicatorLevel::cast($match['indicator']);
                    if ($indicatorLevel->gte($minIndicatorLevel) || $this->io->isVerbose()) {
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

        $this->io->writeln('');

        foreach ($errors as $error) {
            /** array $match */
            $match = $error['match'];

            /** \SplFileInfo $file */
            $file = $error['file'];

            $links = ExtensionScannerUtility::restFilesLinks($match['restFiles']);
            $line = (int) $match['line'];

            $relativePath = trim(str_replace($paths, '', $file->getPathname()), '/');

            $this->io->writeln('<info>'.$relativePath.'</>');
            $this->io->writeln($match['message'] . ' <comment>('.$match['indicator'].')</>');
            $this->io->writeln('<comment>'.$line.'</> '.FileUtility::getLineFromFile($file->getPathname(), $line));
            if (!empty($links)) {
                foreach ($links as $link) {
                    $this->io->writeln($link);
                }
            }
            $this->io->writeln('');
        }

        if (!empty($errors)) {
            return 1;
        }
        return 0;
    }
}
