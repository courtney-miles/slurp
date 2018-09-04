<?php
/**
 * Author: Courtney Miles
 * Date: 14/08/18
 * Time: 10:14 PM
 */

namespace MilesAsylum\Slurp\Tests\Slurp\Transform\SlurpTransformer;

use MilesAsylum\Slurp\Transform\SlurpTransformer\AbstractChangeTransformer;
use MilesAsylum\Slurp\Transform\SlurpTransformer\Change;
use MilesAsylum\Slurp\Transform\SlurpTransformer\TransformerLoader;
use PHPUnit\Framework\TestCase;

class TransformerLoaderTest extends TestCase
{
    /**
     * @var TransformerLoader
     */
    protected $transformerLoader;

    public function setUp()
    {
        $this->transformerLoader = new TransformerLoader();
    }

    public function testLoadTransformer()
    {
        $transformer = $this->transformerLoader->loadTransformer(new Scratch());
        $this->assertInstanceOf(ScratchTransformer::class, $transformer);
        $this->assertSame(
            $transformer,
            $this->transformerLoader->loadTransformer(new Scratch()),
            "The loaded transformer did not return the same instance of the transformer."
        );
    }
}

class Scratch extends Change
{
    /**
     * @return string
     */
    public function transformedBy()
    {
        return ScratchTransformer::class;
    }
}

class ScratchTransformer extends AbstractChangeTransformer
{
    public function transform($value, Change $change)
    {
        // Meh.
    }
}
