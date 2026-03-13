<?php

namespace JobMetric\Extension\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Translation\PotentiallyTranslatedString;
use JobMetric\Extension\Facades\Extension;

/**
 * Class ExtensionExistRule
 *
 * Validates that the given value (extension namespace / class FQCN) exists
 * among discovered extensions of the configured type. The rule:
 *  - Loads extensions for the type passed in the constructor.
 *  - Checks whether the attribute value matches any extension's namespace.
 *  - Fails with a translation message if no match is found.
 *
 * Error messaging relies on translation keys under `extension::base.validation.*`.
 */
readonly class ExtensionExistRule implements ValidationRule
{
    /**
     * Extension type used to filter the list of extensions (e.g. Module).
     *
     * @var string
     */
    private string $type;

    /**
     * Create a new rule instance.
     *
     * @param string $type Extension type to resolve the list (e.g. Module).
     */
    public function __construct(string $type)
    {
        $this->type = $type;
    }

    /**
     * Run the validation rule.
     *
     * @param string $attribute
     * @param mixed $value
     * @param Closure(string): PotentiallyTranslatedString $fail
     *
     * @return void
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if ($value === null || $value === '') {
            return;
        }

        $response = Extension::all(['extension' => $this->type]);
        $extensions = $response->data;

        if (! is_iterable($extensions)) {
            $fail(trans('extension::base.validation.namespace_not_found', ['namespace' => (string) $value]));

            return;
        }

        $found = false;
        foreach ($extensions as $extension) {
            $namespace = $extension['namespace'] ?? null;
            if ($namespace === $value) {
                $found = true;

                break;
            }
        }

        if (! $found) {
            $fail(trans('extension::base.validation.namespace_not_found', ['namespace' => (string) $value]));
        }
    }
}
