# Laravel Rector

Opinionated Rector Rules for Laravel

## Requirements

- PHP `^8.5`
- [Laravel](https://laravel.com/) 13
- [Rector](https://getrector.com/) `^2.5`, installed with this package

## Installation

```bash
composer require zero-to-prod/laravel-rector
```

### Configuration

CLI install. It asks for every value the package can be configured with and
writes `config/laravel-rector.php`:

```bash
php artisan laravel-rector:install
```

Rerunning it is safe: the file reports `created`, `unchanged` or `updated`, and
is only overwritten once you confirm.

To publish the configuration file by itself instead:

```bash
php artisan vendor:publish --tag=laravel-rector-config
```

## Rules

Register the rules you want in `rector.php`:

```php
use Rector\Config\RectorConfig;
use ZeroToProd\LaravelRector\Rector\AddTypeToConstOnReadonlyClassRector;
use ZeroToProd\LaravelRector\Rector\EnforceInvokableControllerRouteRector;
use ZeroToProd\LaravelRector\Rector\RenameParamToMatchTypeExactCaseRector;

return RectorConfig::configure()
    ->withPaths([
        __DIR__.'/app',
        __DIR__.'/routes',
        __DIR__.'/tests',
    ])
    ->withRules([
        AddTypeToConstOnReadonlyClassRector::class,
        EnforceInvokableControllerRouteRector::class,
        RenameParamToMatchTypeExactCaseRector::class,
    ]);
```

### `AddTypeToConstOnReadonlyClassRector`

Constants on a readonly class carry a type, whether the class is final or not.
A constant a parent already declares is left alone: the type it is given there
is the one that counts.

```diff
 readonly class SomeModel
 {
-    public const name = 'name';
+    public const string name = 'name';
 }
```

### `EnforceInvokableControllerRouteRector`

Controllers are invokable: a route maps to a class, never to a method on one.

```diff
-Route::get('/user', [UserShowController::class, '__invoke']);
+Route::get('/user', UserShowController::class);
```

Every other action that names a method — an array callable, an `@` string,
`Route::resource()`, `Route::controller()` — has no invokable equivalent to
rewrite it to, so the rule reports it as an error naming the file and line
instead of changing it.

### `RenameParamToMatchTypeExactCaseRector`

A parameter typed with a class is named after that class, in the class's own
casing. Methods that override a parent or interface declaration are left alone:
their parameter names are part of a contract.

```diff
 final class SomeClass
 {
-    public function run(Apple $pie)
+    public function run(Apple $Apple)
     {
-        $food = $pie;
+        $food = $Apple;
     }
 }
```

## Agent development

The package registers an [MCP](https://modelcontextprotocol.io/) server so
coding agents can read how it is meant to be used. It requires
[`laravel/mcp`](https://github.com/laravel/mcp), and registers nothing without
it.

```bash
composer require --dev laravel/mcp
php artisan mcp:start laravel-rector
```

Register it with your agent:

```bash
claude mcp add laravel-rector -- php artisan mcp:start laravel-rector
```

Three tools are exposed:

- `readme` — this document.
- `api` — the exact signature of every public class, property and method.
  Anything unlisted is internal and may change in any release.
- `install` — what `laravel-rector:install` does, without a prompt to answer.
  Takes `enabled` and `handle`, each defaulting to the current setting, and
  writes `config/laravel-rector.php`. A file that already says something else
  is left alone and reported until the call passes `overwrite: true`.

Point the handle somewhere else, or turn the server off, in
`config/laravel-rector.php`:

```php
'mcp' => [
    'enabled' => true,
    'handle' => 'laravel-rector',
],
```

## Development

```bash
composer check   # lint, rector, phpstan, 100% coverage, bc-check — mutates nothing
composer fix     # rector then pint
composer mcp list                      # the server's tools
composer mcp call api '{}'             # call one
```

`composer check` requires a coverage driver (Xdebug or pcov); without one Pest
cannot satisfy the `--min=100` gate.

## License

MIT. See [LICENSE](LICENSE).
