#!/usr/bin/env sh

set -e

TOOL_DIR=build/bc-check
BIN="$TOOL_DIR/vendor/bin/roave-backward-compatibility-check"

if [ -z "$(git tag --list '[0-9]*.[0-9]*.[0-9]*' 'v[0-9]*.[0-9]*.[0-9]*')" ]; then
    echo "bc-check: no SemVer release tags found, nothing to compare against. Skipping."
    exit 0
fi

if [ ! -x "$BIN" ]; then
    echo "bc-check: installing roave/backward-compatibility-check into $TOOL_DIR"
    mkdir -p "$TOOL_DIR"
    composer require --working-dir="$TOOL_DIR" \
        roave/backward-compatibility-check --no-interaction --no-progress
fi

# rector/rector declares no PSR-4 autoload (its classes are loaded by a scoped
# bootstrap.php), so BetterReflection cannot resolve Rector\Rector\AbstractRector and
# reports every rule class as "[BC] SKIPPED". Roave counts those skips as changes and
# exits 3, so drop them and fail only on real findings.
if OUTPUT=$("$BIN" "$@" 2>&1); then
    STATUS=0
else
    STATUS=$?
fi

FILTERED=$(printf '%s\n' "$OUTPUT" | grep -v '^\[BC\] SKIPPED' || true)
SKIPPED=$(printf '%s\n' "$OUTPUT" | grep -c '^\[BC\] SKIPPED' || true)
FINDINGS=$(printf '%s\n' "$FILTERED" | grep -c '^\[BC\]' || true)

printf '%s\n' "$FILTERED" >&2

if [ "$SKIPPED" -gt 0 ]; then
    echo "bc-check: ignored $SKIPPED unresolvable-symbol skip(s)." >&2
fi

if [ "$STATUS" -eq 3 ] && [ "$FINDINGS" -eq 0 ]; then
    echo "bc-check: no backwards-incompatible changes detected (the count above only totals the skips)." >&2
    exit 0
fi

exit "$STATUS"