<?php declare(strict_types=1);

namespace App\Enums;

use BenSampo\Enum\Enum;

/**
 * @method static static OptionOne()
 * @method static static OptionTwo()
 * @method static static OptionThree()
 */
final class SportEnum extends Enum
{
    const football = 1;

    const basketball = 2;

    /* const volleyball = 3;

    const tennis = 4; */

    //const futsal = 5;

    //const ice_hockey = 6;
}
