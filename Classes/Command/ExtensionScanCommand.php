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
 * Command "extension:scan"
 */
class ExtensionScanCommand extends Command
{
    protected function configure()
    {
        $this
            ->setDescription('Scans files for code deprecations')
            ->addOption('exit-on-error', null, InputOption::VALUE_NONE, 'Exit immediatly on first error')
            ->addOption('file-name', null, InputOption::VALUE_OPTIONAL, 'File name pattern', '*.php')
            ->addOption('min-indicator-level', null, InputOption::VALUE_OPTIONAL, 'Minimum level for return error exit code', 'strong')
            ->addArgument('paths', InputArgument::IS_ARRAY | InputArgument::REQUIRED, 'Path for scanning files')
        ;
    }

    protected function initialize(InputInterface $input, OutputInterface $output)
    {
    }

    protected function interact(InputInterface $input, OutputInterface $output)
    {
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('Scanning for deprecations');

        $path = $input->getArgument('paths');
        $fileNamePattern = $input->getOption('file-name');
        $minIndicatorLevel = IndicatorLevel::cast($input->getOption('min-indicator-level'));
        $exitOnError = $input->getOption('exit-on-error');
        $hasError = false;

        $finder = new Finder();
        $files = $finder->files()->in($path)->name($fileNamePattern)->sortByName();
        foreach ($files as $file) {
            $absoluteFilePath = $file->getPathname();
            $relativePath = trim(str_replace($path, '', $absoluteFilePath), '/');


            $matches = ExtensionScannerUtility::scanFile($absoluteFilePath);
            if (empty($matches)) {
                if ($io->isVeryVerbose()) {
                    $io->success($relativePath);
                }
            } else {
                foreach ($matches as $match) {

                    $restFiles = [];
                    foreach ($match['restFiles'] as $restFile) {
                        foreach (['9.6', '9.5', '9.4', '9.2', '9.1', '9.0'] as $version) {
                            $rst = GeneralUtility::getFileAbsFileName('EXT:core/Documentation/Changelog/'.$version.'/'.$restFile);
                            if (file_exists($rst)) {
                                $restFiles[] = 'https://docs.typo3.org/c/typo3/cms-core/master/en-us/Changelog/' . $version . '/' . basename($restFile, '.rst') . '.html';
                                break;
                            }
                        }
                    }
                    // array_walk($match['restFiles'], function(&$value){
                    //     $value = substr($value, 12, strpos($value, '-', 12) - 12);
                    // });

                    // $message = $match['message']
                    //     . ' in ' . $relativePath . ':' . $match['line']
                    //     . "\n - " . join("\n - ", $restFiles)
                    // ;
                    $indicatorLevel = IndicatorLevel::cast($match['indicator']);

                    // $io->block($message, (string) $indicatorLevel);
                    $io->writeln('<fg=white;bg=red>[' . (string) $indicatorLevel . ']</> ' . $match['message']);
                    $io->writeln($relativePath . ':' . $match['line']);
                    $io->writeln($restFiles);

                    if ($indicatorLevel->gte($minIndicatorLevel)) {
                        // $io->error($message);
                        $hasError = true;
                        if ($exitOnError) {
                            break 2;
                        }
                    } else if ($io->isVerbose()){
                        // $io->warning($message);
                    }
                }
            }
        }
        return $hasError ? 1: 0;
    }


}