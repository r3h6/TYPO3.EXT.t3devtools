<?php

namespace R3H6\T3devtools\Seeder;

use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\DataHandling\DataHandler;

class Data extends \ArrayObject
{
    /**
     * @var \TYPO3\CMS\Core\DataHandling\DataHandler
     */
    protected $dataHandler;

    public function __construct(DataHandler $dataHandler = null)
    {
        $this->dataHandler = $dataHandler ?? GeneralUtility::makeInstance(DataHandler::class);
    }

    public function commit()
    {
        $data = $this->exchangeArray([]);
        print_r($data);
        // exit;

        $GLOBALS['BE_USER']->backendCheckLogin();
        // $GLOBALS['BE_USER']->user['admin'] = true;
        $this->dataHandler->start($data, []);
        $this->dataHandler->process_datamap();
        $this->dataHandler->clear_cacheCmd('pages');

        if (!empty($this->dataHandler->errorLog)) {
            throw new \Exception('Could not update record: '.implode(', ', $this->dataHandler->errorLog), 1479071815);
        }
    }
}
