<?php
/**
 * Author: Courtney Miles
 * Date: 22/08/18
 * Time: 10:38 PM
 */

namespace MilesAsylum\Slurp\Stage;


use MilesAsylum\Slurp\Load\LoaderInterface;
use MilesAsylum\Slurp\SlurpPayload;

class LoadStage extends AbstractStage
{
    /**
     * @var LoaderInterface
     */
    private $loader;

    /**
     * @var SlurpPayload
     */
    protected $payload;

    protected $loadAborted = false;

    public function __construct(LoaderInterface $loader)
    {
        $this->loader = $loader;
    }

    public function __invoke(SlurpPayload $payload): SlurpPayload
    {
        if (!$this->loader->hasBegun()) {
            $this->loader->begin();
        }

        if (!$this->loadAborted) {
            if ($payload->hasViolations()) {
                $this->loader->abort();
                $this->loadAborted = true;
                $payload->setLoadAborted($this->loadAborted);
            } else {
                $this->loader->loadValues($payload->getValues());
            }
        } else {
            $payload->setLoadAborted(true);
        }

        $this->payload = $payload;
        $this->notify();

        return $payload;
    }

    public function getPayload(): SlurpPayload
    {
        return $this->payload;
    }
}
