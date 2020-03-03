<?php
declare(strict_types = 1);
namespace R3H6\T3devtools\Utility;


class IndicatorLevel extends \TYPO3\CMS\Core\Type\Enumeration
{

    const NONE = 'none';
    const WEAK = 'weak';
    const STRONG = 'strong';

    const __default = self::NONE;

    private static $levels = [
        self::NONE => 0,
        self::WEAK => 1,
        self::STRONG => 9,

    ];

    public function gte(self $indicator)
    {
        return static::$levels[$this->value] >= static::$levels[$indicator->value];
    }
}
