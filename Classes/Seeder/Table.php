<?php

namespace R3H6\T3devtools\Seeder;

use R3H6\T3devtools\Seeder\File;
use R3H6\T3devtools\Seeder\Faker;
use R3H6\T3devtools\Seeder\DatabaseSeeder;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\DataHandling\DataHandler;

class Table
{
    /**
     * @var string
     */
    protected $table;

    /**
     * @var int
     */
    protected $defaultPid;

    /**
     * @var \ArrayObject
     */
    protected $seeds;

    /**
     * @var \TYPO3\CMS\Core\DataHandling\DataHandler
     */
    protected $dataHandler;

    public function __construct($table, $defaultPid = 1, Faker $faker = null)
    {
        $this->table = $table;
        $this->defaultPid = $defaultPid;
        $this->seeds = new \ArrayObject();
        $this->faker = $faker ?? GeneralUtility::makeInstance(Faker::class);
        $this->dataHandler = GeneralUtility::makeInstance(DataHandler::class);
    }

    public function create(int $count): self
    {
        for ($i = 0; $i < $count; $i++) {
            $newSeed = GeneralUtility::makeInstance(Seed::class, $this->table, [
                'pid' => $this->defaultPid,
            ]);
            $this->add($newSeed);
        }
        return $this;
    }

    public function csv()
    {
        $ids = [];
        foreach ($this->seeds as $seed) {
            $ids[] = $seed->getIdentifier();
        }
        return join(',', $ids);
    }

    public function add(Seed $seed): self
    {
        if ($seed->getTable() !== $this->table) {
            throw new \InvalidArgumentException('Seed does not belon to table '.$this->table, 1584270271487);
        }
        $this->seeds[$seed->getHash()] = $seed;
        return $this;
    }

    public function each($callback): self
    {
        foreach ($this->seeds as $seed) {
            call_user_func($callback, $seed, $this->faker);
        }
        return $this;
    }

    public function random($amount): self
    {
        $newSet = new self($this->table, $this->defaultPid, $this->faker);
        $seeds = $this->seeds->getArrayCopy();
        $keys = (array) array_rand($seeds, $amount);
        foreach ($keys as $key) {
            $newSet->add($seeds[$key]);
        }

        return $newSet;
    }

    public function one(): self
    {
        return $this->random(1);
    }

    public function localize($locale, $sysLanguageUid): self
    {

        $faker = GeneralUtility::makeInstance(Faker::class, $locale);
        $localizedSet = new self($this->table, $this->defaultPid, $faker);

        $this->commit();

        $disabledField = $GLOBALS['TCA'][$this->table]['ctrl']['enablecolumns']['disabled'] ?? null;

        foreach ($this->seeds as $seed) {
            $localizedUid = $this->dataHandler->localize($seed->getTable(), $seed->getIdentifier(), $sysLanguageUid);
            $localizedSeed = new Seed($seed->getTable(), [], $localizedUid);
            if ($disabledField !== null) {
                $localizedSeed[$disabledField] = 0;
            }
            $localizedSet->add($localizedSeed);
        }

        if ($disabledField !== null) {
            $localizedSet->commit();
        }

        return $localizedSet;
    }

    protected function fillData (&$data, &$substituteIdsMap)
    {
        foreach ($this->seeds as $seed) {
            $seedId = $seed->getIdentifier();
            if (!is_numeric($seedId)) {
                $substituteIdsMap[$seedId] = $seed;
            }
            foreach ($seed as $propertyName => $propertyValue) {
                if ($propertyValue instanceof Table) {
                    // $seeds = $propertyValue->seeds->getArrayCopy() + $seeds;
                    $propertyValue->fillData($data, $substituteIdsMap);
                    $propertyValueString = $propertyValue->csv();
                } else if ($propertyValue instanceof File) {
                    $files = $propertyValue->getFiles();
                    $ids = [];
                    foreach ($files as $file) {
                        $id = uniqid('NEW');
                        $ids[] = $id;
                        $data['sys_file_reference'][$id] = [
                            'pid' => $seed['pid'],
                            'table_local' => 'sys_file',
                            'uid_local' => $file->getUid(),
                            'tablenames' => $seed->getTable(),
                            'uid_foreign' => $seed->getIdentifier(),
                            'fieldname' => $propertyName,
                        ];
                    }
                    $propertyValueString = join(',', $ids);
                } else {
                    $propertyValueString = $propertyValue;
                }
                $data[$seed->getTable()][$seedId][$propertyName] = $propertyValueString;
            }
        }
    }

    public function commit(): self
    {
        $data = [];
        $substituteIdsMap = [];


        $this->fillData($data, $substituteIdsMap);

        // print_r($data);
        // exit;

/*
        $seeds = $this->seeds->getArrayCopy();
        foreach ($seeds as $seed) {
            foreach ($seed as $propertyName => $propertyValue) {
                if ($propertyValue instanceof Table) {
                    $seeds = $propertyValue->seeds->getArrayCopy() + $seeds;
                    $seed[$propertyName] = $propertyValue->csv();
                }
                if ($propertyValue instanceof File) {
                    $files = $propertyValue->getFiles();
                    $ids = [];
                    foreach ($files as $file) {
                        $id = uniqid('NEW');
                        $ids[] = $id;
                        $data['sys_file_reference'][$id] = [
                            'pid' => $seed['pid'],
                            'table_local' => 'sys_file',
                            'uid_local' => $file->getUid(),
                            'tablenames' => $seed->getTable(),
                            'uid_foreign' => $seed->getIdentifier(),
                            'fieldname' => $propertyName,
                        ];
                    }
                    $seed[$propertyName] = join(',', $ids);
                }
            }
        }

        foreach ($seeds as $seed) {
            $id = $seed->getIdentifier();
            if (!is_numeric($id)) {
                $substituteIdsMap[$id] = $seed;
            }
            $properties = $seed->getArrayCopy();
            if (!empty($properties)) {
                $data[$seed->getTable()][$id] = $properties;
            }
            $seed->reset();
        }
*/
        $GLOBALS['BE_USER']->backendCheckLogin();
        // $GLOBALS['BE_USER']->user['admin'] = true;
        $this->dataHandler->start($data, []);
        $this->dataHandler->process_datamap();
        $this->dataHandler->clear_cacheCmd('pages');

        if (!empty($this->dataHandler->errorLog)) {
            throw new \Exception('Could not update record: '.implode(', ', $this->dataHandler->errorLog), 1479071815);
        }

        if (!empty($this->dataHandler->substNEWwithIDs)) {
            foreach($this->dataHandler->substNEWwithIDs as $tmpId => $uid) {
                if (isset($substituteIdsMap[$tmpId])) {
                    /** \R3H6\T3devtools\Seeder\Seed $seed */
                    $seed = $substituteIdsMap[$tmpId];
                    $seed->setUid((int) $uid);
                }
            }
        }
        return $this;
    }

    public function __destruct()
    {
        $this->commit();
    }
}