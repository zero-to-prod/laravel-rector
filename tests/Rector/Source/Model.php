<?php

declare(strict_types=1);

namespace ZeroToProd\LaravelRector\Tests\Rector\Source;

/** Gives the fixture traits a user PHPStan can analyse them through. */
final class Model
{
    use DataModel;
    use Timestamps;

    public string $name = 'name';
}
