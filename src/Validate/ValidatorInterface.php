<?php
/**
 * @author Courtney Miles
 *
 * @see https://github.com/courtney-miles/slurp
 *
 * @package milesasylum/slurp
 *
 * @license MIT
 */

declare(strict_types=1);

namespace MilesAsylum\Slurp\Validate;

interface ValidatorInterface
{
    /**
     * @param int $recordId
     * @param string $field
     * @param $value
     *
     * @return ViolationInterface[]
     */
    public function validateField(int $recordId, string $field, $value): array;

    /**
     * @param int $recordId
     * @param array $record
     *
     * @return ViolationInterface[]
     */
    public function validateRecord(int $recordId, array $record): array;
}
