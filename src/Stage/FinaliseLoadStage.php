<?php
/**
 * Author: Courtney Miles
 * Date: 26/08/18
 * Time: 11:25 AM
 */

namespace MilesAsylum\Slurp\Stage;


use MilesAsylum\Slurp\Load\LoaderInterface;
use MilesAsylum\Slurp\Slurp;

class FinaliseLoadStage implements OuterProcessStageInterface
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
        $this->loader->finalise();

        return $slurp;
    }
}
