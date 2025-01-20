<?php

namespace JobMetric\Extension\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Translation\PotentiallyTranslatedString;
use JobMetric\Extension\Facades\Extension;

class ExtensionExistRule implements ValidationRule
{
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct(
        private readonly string $type
    )
    {
    }

    /**
     * Run the validation rule.
     *
     * @param Closure(string): PotentiallyTranslatedString $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $extensions = Extension::all($this->type);

        $key = null;
        foreach ($extensions as $extension_key => $extension) {
            if ($extension['namespace'] === $value) {
                $key = $extension_key;
                break;
            }
        }

        if (is_null($key)) {
            $fail(__('extension::base.validation.namespace_not_found', ['namespace' => $value]));
        }
    }
}
