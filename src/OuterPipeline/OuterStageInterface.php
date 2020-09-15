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

use MilesAsylum\Slurp\Slurp;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

interface OuterStageInterface
{
    public function __invoke(Slurp $slurp): Slurp;

    public function setEventDispatcher(EventDispatcherInterface $dispatcher): void;

    public function getState(): string;
}
