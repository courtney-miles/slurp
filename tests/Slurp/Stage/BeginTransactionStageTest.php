<?php
/**
 * Author: Courtney Miles
 * Date: 24/08/18
 * Time: 10:34 PM
 */

namespace MilesAsylum\Slurp\Tests\Slurp\Stage;

use MilesAsylum\Slurp\Stage\BeginTransactionStage;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Mockery\MockInterface;
use PHPUnit\Framework\TestCase;

class BeginTransactionStageTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    /**
     * @var BeginTransactionStage
     */
    protected $stage;

    /**
     * @var \PDO|MockInterface
     */
    protected $mockPdo;

    public function setUp()
    {
        parent::setUp();

        $this->mockPdo = \Mockery::mock(\PDO::class);

        $this->stage = new BeginTransactionStage($this->mockPdo);
    }

    public function testBeginTransactionOnInvoke()
    {
        $this->mockPdo->shouldReceive('beginTransaction')
            ->once();

        ($this->stage)();
    }
}
