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

<!-- rules:start -->

## Rules

- [`AddTypeToConstOnReadonlyClassRector`](#addtypetoconstonreadonlyclassrector) — Add type to constants on readonly classes regardless of final
- [`EnforceInvokableControllerRouteRector`](#enforceinvokablecontrollerrouterector) — Routes must map to an invokable controller class, never to a method
- [`ForbidTodoAnnotationRector`](#forbidtodoannotationrector) — Comments must not carry a TODO annotation
- [`RenameParamToMatchTypeExactCaseRector`](#renameparamtomatchtypeexactcaserector) — Rename param to match class type hint exactly (PascalCase)

Register the rules you want in `rector.php`:

```php
use Rector\Config\RectorConfig;
use ZeroToProd\LaravelRector\Rector\AddTypeToConstOnReadonlyClassRector;
use ZeroToProd\LaravelRector\Rector\EnforceInvokableControllerRouteRector;
use ZeroToProd\LaravelRector\Rector\ForbidTodoAnnotationRector;
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
        ForbidTodoAnnotationRector::class,
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

Configured with:

```php
->withConfiguredRule(AddTypeToConstOnReadonlyClassRector::class, [
    'leave_todo' => true,
])
```

```diff
 readonly class SomeModel
 {
+    // TODO: type this constant as string
     public const name = 'name';
 }
```

### `EnforceInvokableControllerRouteRector`

Controllers are invokable: a route maps to a class, never to a method on one.

`[Controller::class, '__invoke']` is the same route written the long way, so
it is rewritten to `Controller::class`. Every other action that names a method
— an array callable, an `@` string, `Route::resource()`,
`Route::controller()` — has no invokable equivalent to rewrite it to and is
reported as an error instead.

```diff
-Route::get('/user', [UserShowController::class, '__invoke']);
+Route::get('/user', UserShowController::class);
```

Configured with:

```php
->withConfiguredRule(EnforceInvokableControllerRouteRector::class, [
    'leave_todo' => true,
])
```

```diff
+// TODO: Route action names __invoke. Pass the controller class itself.
 Route::get('/user', [UserShowController::class, '__invoke']);
```

### `ForbidTodoAnnotationRector`

A TODO annotation is a note that the work is not finished, left where nothing
tracks it.

There is nothing to rewrite it to, so every comment carrying one is reported
as an error naming the file and line: finish the work, or record it where the
team can see it.

Every casing is caught, in a line comment, a hash comment or a docblock. The
annotation is read from the file's comment tokens, so one written inside a
string or a heredoc is not a violation.

Configured with `leave_todo`, the rule reports nothing at all: the note it
would leave is the comment it just found.

```diff
-// @TODO handle the empty case
-return $items[0];
+return $items[0] ?? null;
```

### `RenameParamToMatchTypeExactCaseRector`

A parameter typed with a class is named after that class, in the class's own
casing.

Methods that override a parent or interface declaration are left alone: their
parameter names are part of a contract this rule has no business rewriting.

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

Configured with:

```php
->withConfiguredRule(RenameParamToMatchTypeExactCaseRector::class, [
    'leave_todo' => true,
])
```

```diff
 final class SomeClass
 {
+    // TODO: rename $pie to $Apple, after its type
     public function run(Apple $pie)
     {
         $food = $pie;
     }
 }
```

<!-- rules:end -->

## Options

Every rule takes one option, `leave_todo`. Configured with it, a rule stops
changing the code and stops reporting an error: it leaves a comment naming the
violation where it found it, so the change stays a decision for whoever reads
the file.

```php
->withConfiguredRule(EnforceInvokableControllerRouteRector::class, [
    EnforceInvokableControllerRouteRector::LEAVE_TODO => true,
])
```

The comment is left on the statement the violation sits on, and running twice
leaves one comment rather than two, so the option is safe to run in a loop
while the todos are worked off. `ForbidTodoAnnotationRector` is the exception
that proves the rule: configured this way it reports nothing at all, because
the note it would leave is the comment it just found.

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
composer check   # lint, rector, phpstan, docs, 100% coverage, bc-check — mutates nothing
composer fix     # rector, pint, then the docs
composer mcp list                      # the server's tools
composer mcp call api '{}'             # call one
```

The Rules section above is generated from the rules themselves: their class
names, class doc comments and rule definitions. Everything between the
`rules:start` and `rules:end` markers is written by `composer docs`, and
`composer docs-check` fails when it no longer matches. Document a rule by
writing its doc comment and its `getRuleDefinition()`, then run `composer fix`.

`composer check` requires a coverage driver (Xdebug or pcov); without one Pest
cannot satisfy the `--min=100` gate.

## License

MIT. See [LICENSE](LICENSE).
