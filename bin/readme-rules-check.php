<?php

declare(strict_types=1);

use ZeroToProd\LaravelRector\Internal\RuleDocumentation;

require_once dirname(__DIR__).'/vendor/autoload.php';

$path = RuleDocumentation::path();
$current = (string) file_get_contents($path);

if (RuleDocumentation::apply($current, RuleDocumentation::section()) !== $current) {
    fwrite(STDERR, "docs-check: the README rules section no longer matches the rules. Run `composer docs`.\n");

    exit(1);
}

echo "docs-check: the README rules section matches the rules.\n";
