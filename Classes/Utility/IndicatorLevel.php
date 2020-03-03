<?php
declare(strict_types = 1);
namespace R3H6\T3devtools\Utility;

use TYPO3\CMS\Core\Utility\MathUtility;

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
  * IndicatorLevel
  */
class IndicatorLevel extends \TYPO3\CMS\Core\Type\Enumeration
{

    const WEAK = 1;
    const STRONG = 2;
    const IGNORE = 9;

    const __default = self::IGNORE;

    public static function cast($value)
    {
        if (is_string($value)) {
            $constants = static::getConstants();
            $value = $constants[strtoupper($value)] ?? (int)$value;
        }

        return parent::cast($value);
    }

    public function gte(self $indicator)
    {
        return $this->value >= $indicator->value;
    }
}
