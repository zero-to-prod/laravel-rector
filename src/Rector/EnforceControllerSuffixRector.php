<?php

declare(strict_types=1);

namespace ZeroToProd\LaravelRector\Rector;

use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use Rector\Contract\Rector\ConfigurableRectorInterface;
use Rector\Exception\ShouldNotHappenException;
use Rector\Rector\AbstractRector;
use Symplify\RuleDocGenerator\Contract\DocumentedRuleInterface;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
use ZeroToProd\LaravelRector\Concerns\LeavesTodo;
use ZeroToProd\LaravelRector\Internal\BootedApplication;
use ZeroToProd\LaravelRector\Internal\RoutedControllers;

/**
 * A controller says so in its name: the class a route maps to ends in `Controller`, so the
 * class behind `GET /user` is `UserShowController`.
 *
 * The application's own routes decide what a controller is. The rule asks the router what
 * every registered route maps to, booting the application to do it, so a class is held to
 * the convention because a request reaches it rather than because of where it is filed. A
 * class no route maps to is left alone, and so is a route mapping to a closure: it names no
 * class to hold to anything.
 *
 * Renaming a class moves every reference to it — the route, the tests, the container
 * bindings — and none of them is in the file that declares it, so there is nothing here to
 * rewrite. The class is reported as an error naming the file and line, and the rename is
 * yours to make.
 *
 * The application is booted from the directory Rector was run in, which is the application
 * root. Configured with `base_path`, it is booted from there instead.
 */
final class EnforceControllerSuffixRector extends AbstractRector implements ConfigurableRectorInterface, DocumentedRuleInterface
{
    use LeavesTodo {
        configure as private configureLeavesTodo;
    }

    public const string BASE_PATH = 'base_path';

    private const string SUFFIX = 'Controller';

    private ?string $basePath = null;

    /** @var array<string, string>|null Controller class, to the first route reaching it */
    private ?array $routes = null;

    /** @param  mixed[]  $configuration */
    public function configure(array $configuration): void
    {
        $this->configureLeavesTodo($configuration);

        $basePath = $configuration[self::BASE_PATH] ?? null;

        $this->basePath = is_string($basePath) ? $basePath : null;
    }

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Controllers must be named with a Controller suffix', [
            new CodeSample(
                <<<'CODE_SAMPLE'
                    // Route::get('/user', UserShow::class);

                    readonly class UserShow
                    {
                        public function __invoke(User $User): View
                        {
                            return view('user.show', ['user' => $User]);
                        }
                    }
                    CODE_SAMPLE,
                <<<'CODE_SAMPLE'
                    // Route::get('/user', UserShowController::class);

                    readonly class UserShowController
                    {
                        public function __invoke(User $User): View
                        {
                            return view('user.show', ['user' => $User]);
                        }
                    }
                    CODE_SAMPLE,
            ),
            new ConfiguredCodeSample(
                <<<'CODE_SAMPLE'
                    // Route::get('/user', UserShow::class);

                    readonly class UserShow
                    {
                        public function __invoke(User $User): View
                        {
                            return view('user.show', ['user' => $User]);
                        }
                    }
                    CODE_SAMPLE,
                <<<'CODE_SAMPLE'
                    // Route::get('/user', UserShow::class);

                    // TODO: Class "UserShow" is the controller for route "GET /user" and does not end in Controller. Rename it UserShowController.
                    readonly class UserShow
                    {
                        public function __invoke(User $User): View
                        {
                            return view('user.show', ['user' => $User]);
                        }
                    }
                    CODE_SAMPLE,
                [self::LEAVE_TODO => true],
            ),
        ]);
    }

    /** @return array<class-string<Node>> */
    public function getNodeTypes(): array
    {
        return [Class_::class];
    }

    /**
     * @param  Class_  $node
     *
     * @throws ShouldNotHappenException
     */
    public function refactor(Node $node): ?Class_
    {
        // An anonymous class is a route action no route registers by name
        $name = $node->name?->toString();

        if ($name === null || str_ends_with($name, self::SUFFIX)) {
            return null;
        }

        $route = $this->routes()[(string) $this->getName($node)] ?? null;

        if ($route === null) {
            return null;
        }

        $violation = sprintf(
            'Class "%s" is the controller for route "%s" and does not end in %s. Rename it %s%s.',
            $name,
            $route,
            self::SUFFIX,
            $name,
            self::SUFFIX,
        );

        if (! $this->leavesTodo()) {
            throw new ShouldNotHappenException(sprintf('%s See %s:%d', $violation, $this->file->getFilePath(), $node->getStartLine()));
        }

        return $this->annotate($node, $violation);
    }

    /**
     * Every class the application routes to, against the first route reaching it. The
     * application is booted the first time a file is read, and answers for the rest.
     *
     * @return array<string, string>
     *
     * @throws ShouldNotHappenException
     */
    private function routes(): array
    {
        return $this->routes ??= $this->resolve($this->basePath ?? (string) getcwd());
    }

    /**
     * @return array<string, string>
     *
     * @throws ShouldNotHappenException
     */
    private function resolve(string $basePath): array
    {
        BootedApplication::at($basePath);

        $routes = [];

        foreach (RoutedControllers::all() as $RoutedController) {
            $routes[$RoutedController->class] ??= $RoutedController->route();
        }

        return $routes;
    }
}
