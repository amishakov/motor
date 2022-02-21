<?php

declare(strict_types=1);

namespace App\Services;

class Setting
{
    private array $settings;

    public function __construct(array $settings)
    {
        $this->settings = $settings;
    }

    /**
     * Get settings
     *
     * @return array
     */
    public function all(): array
    {
        return $this->settings;
    }

    /**
     * Get setting by key
     *
     * @param string|null $key
     * @param mixed|null  $default
     *
     * @return mixed
     */
    public function get(?string $key = null, mixed $default = null): mixed
    {
        $array = $this->settings;

        if (! str_contains($key, '.')) {
            return $array[$key] ?? $default;
        }

        foreach (explode('.', $key) as $segment) {
            if (is_array($array) && array_key_exists($segment, $array)) {
                $array = $array[$segment];
            } else {
                return $default;
            }
        }

        return $array;
    }
}
