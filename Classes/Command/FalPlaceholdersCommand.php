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

use R3H6\T3devtools\Generator\FakeFileGenerator;
use TYPO3\CMS\Core\Resource\File;
use TYPO3\CMS\Core\Utility\PathUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Console\Command\Command;
use TYPO3\CMS\Core\Resource\StorageRepository;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Command "fal:placeholders"
 */
class FalPlaceholdersCommand extends AbstractCommand
{
    protected const OPTION_DRYRUN = 'dry-run';

    protected function configure()
    {
        $this
            ->setDescription('Mocks missing local files')
            ->addOption(self::OPTION_DRYRUN, null, InputOption::VALUE_NONE, 'Only print messages')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $isDryRun = $input->getOption(self::OPTION_DRYRUN);

        $storageRepository = GeneralUtility::makeInstance(ObjectManager::class)->get(StorageRepository::class);
        /** \TYPO3\CMS\Core\Resource\ResourceStorage[] $storages */
        $storages = $storageRepository->findAll();

        $generator = GeneralUtility::makeInstance(FakeFileGenerator::class);
        $filesystem = GeneralUtility::makeInstance(Filesystem::class);

        $count = 0;
        foreach ($storages as $storage) {
            if ($this->io->isVerbose()) {
                $this->io->writeln('Storage '.$storage);
            }

            $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable('sys_file');
            $statement = $queryBuilder
                ->select('*')
                ->from('sys_file')
                ->where(
                    $queryBuilder->expr()->eq('storage', $queryBuilder->createNamedParameter($storage->getUid(), \PDO::PARAM_INT))
                )
                ->execute();

            while ($row = $statement->fetch()) {
                /** \TYPO3\CMS\Core\Resource\File $file */
                $file = GeneralUtility::makeInstance(File::class, $row, $storage);
                $filePath = $file->getForLocalProcessing(false);
                if (false === $filesystem->exists($filePath)) {
                    if ($output->isVerbose()) {
                        $this->io->writeln('Fake '.$file->getPublicUrl());
                    }
                    try {
                        if (!$isDryRun) {
                            $generator->create($file, $filePath);
                        }
                        $count += 1;
                    } catch(\Exception $exception) {
                        $this->io->error($exception->getMessage());
                    }
                }
            }
        }

        $this->io->success('Created '.$count.' fake files');
    }
}