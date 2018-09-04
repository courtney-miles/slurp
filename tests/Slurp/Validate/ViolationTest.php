<?php
/**
 * Author: Courtney Miles
 * Date: 4/09/18
 * Time: 6:42 AM
 */

namespace MilesAsylum\Slurp\Tests\Slurp\Validate;

use MilesAsylum\Slurp\Validate\Violation;
use PHPUnit\Framework\TestCase;

class ViolationTest extends TestCase
{
    /**
     * @var Violation
     */
    protected $violation;

    protected $recordId;

    protected $field;

    protected $value;

    protected $message;

    public function setUp()
    {
        parent::setUp();

        $this->recordId = 123;
        $this->field = 'foo';
        $this->value = 'abc';
        $this->message = 'Value abc is invalid.';

        $this->violation = new Violation(
            $this->recordId,
            $this->field,
            $this->value,
            $this->message
        );
    }

    public function testGetRecordId()
    {
        $this->assertSame($this->recordId, $this->violation->getRecordId());
    }

    public function testGetField()
    {
        $this->assertSame($this->field, $this->violation->getField());
    }

    public function testGetValue()
    {
        $this->assertSame($this->value, $this->violation->getValue());
    }

    public function testGetMessage()
    {
        $this->assertSame($this->message, $this->violation->getMessage());
    }
}
