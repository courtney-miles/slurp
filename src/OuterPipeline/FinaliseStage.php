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

namespace MilesAsylum\Slurp\OuterPipeline;

use MilesAsylum\Slurp\Event\ExtractionFinalisationBeginEvent;
use MilesAsylum\Slurp\Event\ExtractionFinalisationCompleteEvent;
use MilesAsylum\Slurp\Load\LoaderInterface;
use MilesAsylum\Slurp\Slurp;

class FinaliseStage extends AbstractOuterStage
{
    /**
     * @var LoaderInterface
     */
    private $loader;

    public function __construct(LoaderInterface $loader)
    {
        $this->loader = $loader;
    }

    public function __invoke(Slurp $slurp): Slurp
    {
        if ($this->loader->hasBegun() && !$slurp->isAborted() && !$this->loader->isAborted()) {
            $this->dispatch(ExtractionFinalisationBeginEvent::NAME, new ExtractionFinalisationBeginEvent());
            $this->loader->finalise();
            $this->dispatch(ExtractionFinalisationCompleteEvent::NAME, new ExtractionFinalisationCompleteEvent());
        }

        return $slurp;
    }
}
