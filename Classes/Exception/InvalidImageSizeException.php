<?php
declare(strict_types = 1);
namespace R3H6\T3devtools\Exception;

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
 * InvalidImageSizeException
 */
class InvalidImageSizeException extends \Exception
{
    public function __construct($filePath)
    {
        parent::__construct('Invalid image size for file '.$filePath, 1583963596987);
    }
}
