<?php
/**
 * Author: Courtney Miles
 * Date: 22/08/18
 * Time: 11:46 PM
 */

declare(strict_types=1);

namespace MilesAsylum\Slurp\OuterPipeline;

use MilesAsylum\Slurp\Slurp;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

interface OuterStageInterface
{
    public function __invoke(Slurp $slurp): Slurp;

    public function setEventDispatcher(EventDispatcherInterface $dispatcher): void;

    public function getState(): string;
}
