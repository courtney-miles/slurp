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
    /**
     * @var string
     */
    protected $field;

    public function __construct(string $field, string $message = "", int $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
        $this->field = $field;
    }

    /**
     * @return string
     */
    public function getField(): string
    {
        return $this->field;
    }
}
