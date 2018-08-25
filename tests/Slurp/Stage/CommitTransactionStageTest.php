<?php
/**
 * Author: Courtney Miles
 * Date: 24/08/18
 * Time: 10:39 PM
 */

namespace MilesAsylum\Slurp\Tests\Slurp\Stage;

use MilesAsylum\Slurp\Stage\CommitTransactionStage;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Mockery\MockInterface;
use PHPUnit\Framework\TestCase;

class CommitTransactionStageTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    /**
     * @var CommitTransactionStage
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

        $this->stage = new CommitTransactionStage($this->mockPdo);
    }

    public function testCommitTransactionOnInvoke()
    {
        $this->mockPdo->shouldReceive('commit')
            ->once();

        ($this->stage)();
    }
}
