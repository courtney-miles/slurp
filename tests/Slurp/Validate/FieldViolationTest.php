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

namespace MilesAsylum\Slurp\Tests\Slurp\Validate;

use MilesAsylum\Slurp\Validate\FieldViolation;
use PHPUnit\Framework\TestCase;

class FieldViolationTest extends TestCase
{
    /**
     * @var FieldViolation
     */
    protected $violation;

    protected $recordId;

    protected $field;

    protected $value;

    protected $message;

    public function setUp(): void
    {
        parent::setUp();

        $this->recordId = 123;
        $this->field = 'foo';
        $this->value = 'abc';
        $this->message = 'Value abc is invalid.';

        $this->violation = new FieldViolation(
            $this->recordId,
            $this->field,
            $this->value,
            $this->message
        );
    }

    public function testGetRecordId(): void
    {
        $this->assertSame($this->recordId, $this->violation->getRecordId());
    }

    public function testGetField(): void
    {
        $this->assertSame($this->field, $this->violation->getField());
    }

    public function testGetValue(): void
    {
        $this->assertSame($this->value, $this->violation->getValue());
    }

    public function testGetMessage(): void
    {
        $this->assertSame($this->message, $this->violation->getMessage());
    }
}
