<?php

namespace ZeroToProd\LaravelRector\Tests\Rector\Violation\ForbidTodoAnnotation;

function first(array $items)
{
    // @TODO handle the empty case
    return $items[0];
}
