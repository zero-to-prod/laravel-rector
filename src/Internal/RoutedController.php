<?php

declare(strict_types=1);

namespace ZeroToProd\LaravelRector\Internal;

/**
 * One route, as the class it maps to.
 *
 * @internal
 */
final readonly class RoutedController
{
    /** @param  list<string>  $methods  The HTTP methods the route answers */
    public function __construct(
        public string $class,
        public string $action,
        public array $methods,
        public string $uri,
        public ?string $name,
    ) {}

    /** The route as it reads in a message: `GET /user`. */
    public function route(): string
    {
        return sprintf('%s /%s', $this->methods[0] ?? 'ANY', ltrim($this->uri, '/'));
    }
}
