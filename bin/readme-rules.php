<?php

declare(strict_types=1);

use ZeroToProd\LaravelRector\Internal\RuleDocumentation;

require_once dirname(__DIR__).'/vendor/autoload.php';

$path = RuleDocumentation::path();
$current = (string) file_get_contents($path);
$generated = RuleDocumentation::apply($current, RuleDocumentation::section());

if ($generated === $current) {
    echo "docs: the README already says what the rules say.\n";

    exit(0);
}

file_put_contents($path, $generated);

echo "docs: the README rules section was rewritten from the rules themselves.\n";
