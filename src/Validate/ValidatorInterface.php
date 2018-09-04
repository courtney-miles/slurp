<?php
/**
 * Author: Courtney Miles
 * Date: 3/09/18
 * Time: 9:36 PM
 */

namespace MilesAsylum\Slurp\Validate;

interface ValidatorInterface
{
    /**
     * @param $recordId
     * @param $field
     * @param $value
     * @return ViolationInterface[]
     */
    public function validateField($recordId, $field, $value): array;

    /**
     * @param $recordId
     * @param array $record
     * @return ViolationInterface[]
     */
    public function validateRecord($recordId, array $record): array;
}
