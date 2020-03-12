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

use Symfony\Component\Finder\Finder;
use R3H6\T3devtools\Console\CommandStyle;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Database\ConnectionPool;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Helhum\Typo3Console\Database\Process\MysqlCommand;

/**
 * Command "database:seed"
 */
class DatabaseSeedCommand extends Command
{
    /**
     * @var \Symfony\Component\Console\Style\SymfonyStyle
     */
    protected $io;

    protected function configure()
    {
        $this
            ->setDescription('Fills database with data.')
            ->addArgument('sources', InputArgument::REQUIRED, 'File or directory')
        ;
    }

    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        $this->io = new CommandStyle($input, $output);
    }

    protected function interact(InputInterface $input, OutputInterface $output)
    {
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $sources = $input->getArgument('sources');

        if (is_file($sources)) {
            $finder = [new \SplFileInfo($sources)];
        } else {
            $finder = new Finder();
            $finder->in($sources)->files()->name(['*.xml', '*.t3d', '*.sql', '*.php']);
        }

        $this->io->progressStart(is_array($finder) ? count($finder) : iterator_count($finder));
        foreach ($finder as $file) {
            $this->io->progressAdvance();
            switch ($file->getExtension()){
                case 'xml':
                case 't3d':
                    // $this->importFromT3d($file);
                    break;
                case 'sql':
                    // $this->importFromSql($file);
                    break;
                case 'php':
                    $this->importFromPhp($file);
                    break;
                default:
                    break;
            }
        }
        $this->io->progressFinish();
    }

    protected function importFromT3d(\SplFileInfo $file)
    {
        $command = $this->getApplication()->find('impexp:import');

        $arguments = [
            'file' => $file->getRealPath(),
            // '--ignorePid' => true,
            // '--updateRecords' => true,
        ];

        $greetInput = new ArrayInput($arguments);
        $returnCode = $command->run($greetInput, $this->io->getOutput());
    }

    protected function importFromSql(\SplFileInfo $file)
    {
        $resource = fopen($file->getRealPath(), 'r');
        $connection = $GLOBALS['TYPO3_CONF_VARS']['DB']['Connections']['Default'];
        $mysqlCommand = new MysqlCommand($connection, [], $this->io->getOutput());
        $exitCode = $mysqlCommand->mysql(
            ['--skip-column-names'],
            $resource
        );
    }

    protected function importFromPhp(\SplFileInfo $file)
    {
        require $file->getRealPath();

        $className = $file->getBasename('.php');

        $seeder = GeneralUtility::makeInstance($className);
        $seeder->run();
    }

    protected function drop()
    {
        $connection = GeneralUtility::makeInstance(ConnectionPool::class)->getConnectionByName('Default');
        $schemaManager = $connection->getSchemaManager();
        $tables = $schemaManager->listTables();

        foreach ($tables as $table) {
            $name = $table->getName();
            $query = 'TRUNCATE ' . $name . ';';
            $connection->executeQuery($query, [], []);
        }
    }
}