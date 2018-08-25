<?php
/**
 * Author: Courtney Miles
 * Date: 22/08/18
 * Time: 10:38 PM
 */

namespace MilesAsylum\Slurp\Stage;


use MilesAsylum\Slurp\Load\LoaderInterface;
use MilesAsylum\Slurp\SlurpPayload;

class LoadStage implements StageInterface
{
    /**
     * @var LoaderInterface
     */
    private $loader;

    public function __construct(LoaderInterface $loader)
    {
        $this->loader = $loader;
    }

    public function __invoke(SlurpPayload $payload): SlurpPayload
    {
        if (!$payload->hasViolations()) {
            $this->loader->loadValues($payload->getValues());
        }

        return $payload;
    }
}
