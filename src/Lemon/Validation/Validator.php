<?php

declare(strict_types=1);

namespace Lemon\Validation;

use Lemon\Contracts\Validation\Validator as ValidatorContract;

class Validator implements ValidatorContract
{
    private Rules $rules;

    public function __construct()
    {
        $this->rules = new Rules();
    }

    /**
     * Returns all rules.
     */
    public function rules(): Rules
    {
        return $this->rules;
    }

    /**
     * Determins whenever given data meets given rules.
     */
    public function validate(array $data, array $ruleset): bool
    {
        foreach ($ruleset as $key => $rules) {
            $rules = $this->resolveRules($rules);
            if (!array_key_exists($key, $data) || 0 === strlen((string) $data[$key])) {
                if (in_array(['optional'], $rules)) {
                    continue;
                }

                return false;
            }
            foreach ($rules as $rule) {
                if ('optional' == $rule[0]) {
                    continue;
                }

                if (!$this->rules->call((string) $data[$key], $rule)) {
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * Converts rules into same array.
     */
    public function resolveRules(string|array $rules): array
    {
        if (is_array($rules)) {
            return $rules;
        }

        // TODO regex or parser
        return array_map(
            fn ($item) => explode(':', $item),
            explode('|', $rules)
        );
    }
}
