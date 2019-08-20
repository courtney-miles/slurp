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

namespace MilesAsylum\Slurp\InnerPipeline;

use MilesAsylum\Slurp\SlurpPayload;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

interface StageInterface
{
    public function __invoke(SlurpPayload $payload): SlurpPayload;

    public function setEventDispatcher(EventDispatcherInterface $dispatcher): void;

    public function getPayload(): SlurpPayload;

    public function getState(): ?string;
}
