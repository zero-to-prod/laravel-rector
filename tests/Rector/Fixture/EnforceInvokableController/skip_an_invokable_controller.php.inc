<?php

namespace ZeroToProd\LaravelRector\Tests\Rector\Fixture\EnforceInvokableController;

readonly class UserShowController
{
    public function __construct(private Users $Users) {}

    public static function middleware(): array
    {
        return ['auth'];
    }

    public function __invoke(string $id): string
    {
        return $this->present($id);
    }

    private function present(string $id): string
    {
        return $id;
    }
}
