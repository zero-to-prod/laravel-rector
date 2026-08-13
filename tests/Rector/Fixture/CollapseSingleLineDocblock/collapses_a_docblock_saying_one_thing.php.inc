<?php

namespace ZeroToProd\LaravelRector\Tests\Rector\Fixture\CollapseSingleLineDocblock;

use ReflectionException;

/**
 * A handler.
 */
class SaysOneThing
{
    /**
     * @var string
     */
    public string $name = 'name';

    public function __construct(
        /**
         * @var int
         */
        private int $count = 0,
    ) {}

    /**
     * @throws ReflectionException
     */
    public function handle(): void
    {
        /**
         * @var string $value
         */
        $value = $this->name;
    }
}

?>
-----
<?php

namespace ZeroToProd\LaravelRector\Tests\Rector\Fixture\CollapseSingleLineDocblock;

use ReflectionException;

/** A handler. */
class SaysOneThing
{
    /** @var string */
    public string $name = 'name';

    public function __construct(
        /** @var int */
        private int $count = 0,
    ) {}

    /** @throws ReflectionException */
    public function handle(): void
    {
        /** @var string $value */
        $value = $this->name;
    }
}

?>
