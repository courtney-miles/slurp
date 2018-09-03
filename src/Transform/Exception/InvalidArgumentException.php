<?php
/**
 * Author: Courtney Miles
 * Date: 3/09/18
 * Time: 9:02 PM
 */

namespace MilesAsylum\Slurp\Transform\Exception;

use MilesAsylum\Slurp\Exception\ExceptionInterface;

class InvalidArgumentException extends \InvalidArgumentException implements ExceptionInterface
{
}