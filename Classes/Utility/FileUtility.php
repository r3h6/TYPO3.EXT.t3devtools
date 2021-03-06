<?php
declare(strict_types = 1);
namespace R3H6\T3devtools\Utility;

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
  * FileUtility
  */
class FileUtility
{
    /**
     * Return a specific line from a file
     *
     * @param $fileName
     * @param $lineNumber
     * @return string
     */
    public function getLineFromFile(string $fileName, int $lineNumber): string
    {
        $file = new \SplFileObject($fileName);
        if (!$file->eof()) {
            $file->seek($lineNumber - 1);
            return trim($file->current());
        }
        return '';
    }
}
