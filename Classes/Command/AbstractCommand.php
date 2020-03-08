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

use R3H6\T3devtools\Console\CommandStyle;
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
 * AbstractCommand
 */
abstract class AbstractCommand extends Command
{
    /**
     * @var \R3H6\T3devtools\Console\CommandStyle
     */
    protected $io;

    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        $this->io = new CommandStyle($input, $output);
    }
}
