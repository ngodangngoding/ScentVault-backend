<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RuleConfig extends Model
{
    protected $fillable = [
        'type',
        'label',
        'min_value',
        'max_value',
    ];

    protected function casts(): array
    {
        return [
            'min_value' => 'float',
            'max_value' => 'float',
        ];
    }

    /**
     * Get label for a given type and value.
     *
     * @param string $type 'temperature' or 'time'
     * @param float $value
     * @return string|null
     */
    public static function getLabelFor(string $type, float $value): ?string
    {
        // Special handling for 'time' type where range can wrap around midnight (e.g. malam: 18-5)
        $rules = self::where('type', $type)->get();

        foreach ($rules as $rule) {
            if ($rule->min_value <= $rule->max_value) {
                // Normal range (e.g. 5-11)
                if ($value >= $rule->min_value && $value < $rule->max_value) {
                    return $rule->label;
                }
            } else {
                // Wrapped range (e.g. 18-5 for malam)
                if ($value >= $rule->min_value || $value < $rule->max_value) {
                    return $rule->label;
                }
            }
        }

        return null;
    }
}
