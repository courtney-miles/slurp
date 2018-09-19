<?php
/**
 * Author: Courtney Miles
 * Date: 3/09/18
 * Time: 9:48 PM
 */

namespace MilesAsylum\Slurp\Validate;

interface ViolationInterface
{
    public function getRecordId(): int;

    public function getField(): string;

    public function getValue();

    public function getMessage(): string;
}
