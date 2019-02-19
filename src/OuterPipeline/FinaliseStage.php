<?php
/**
 * Author: Courtney Miles
 * Date: 26/08/18
 * Time: 11:25 AM
 */

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
        if (!$slurp->isAborted() && !$this->loader->isAborted()) {
            $this->dispatch(ExtractionFinalisationBeginEvent::NAME, new ExtractionFinalisationBeginEvent());
            $this->loader->finalise();
            $this->dispatch(ExtractionFinalisationCompleteEvent::NAME, new ExtractionFinalisationCompleteEvent());
        }

        return $slurp;
    }
}
