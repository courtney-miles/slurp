<?php
/**
 * Author: Courtney Miles
 * Date: 15/08/18
 * Time: 10:45 AM
 */

namespace MilesAsylum\Slurp\Transform\SlurpTransformer;

class Trim extends Change
{
    /**
     * @var string
     */
    private $chars;

    /**
     * @var bool
     */
    private $fromLeft;

    /**
     * @var bool
     */
    private $fromRight;

    /**
     * Trim constructor.
     * @param bool $fromLeft
     * @param bool $fromRight
     * @param string $chars
     */
    public function __construct(bool $fromLeft = true, bool $fromRight = true, string $chars = " \t\n\r\0\x0B")
    {
        $this->chars = $chars;
        $this->fromLeft = $fromLeft;
        $this->fromRight = $fromRight;
    }

    public static function createFromOptions(array $options = []): self
    {
        $defaultOptions = ['fromLeft' => true, 'fromRight' => true, 'chars' => " \t\n\r\0\x0B"];

        $options = array_merge($defaultOptions, array_intersect_key($options, $defaultOptions));

        return new self(...array_values($options));
    }

    /**
     * @return string
     */
    public function getChars(): string
    {
        return $this->chars;
    }

    /**
     * @return bool
     */
    public function fromLeft(): bool
    {
        return $this->fromLeft;
    }

    /**
     * @return bool
     */
    public function fromRight(): bool
    {
        return $this->fromRight;
    }

    /**
     * @return string
     */
    public function transformedBy(): string
    {
        return TrimTransformer::class;
    }
}
