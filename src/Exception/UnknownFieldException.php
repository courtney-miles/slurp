<?php
/**
 * Author: Courtney Miles
 * Date: 3/09/18
 * Time: 9:59 PM
 */

namespace MilesAsylum\Slurp\Exception;

use Throwable;

class UnknownFieldException extends \InvalidArgumentException implements ExceptionInterface
{
    protected $field;

    public function __construct($field, string $message = "", int $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
        $this->field = $field;
    }

    public function getField()
    {
        return $this->field;
    }
}
