<?php

namespace Mercator\Core\Permissions;

class PermissionRegistry
{
    protected array $permissions = [];

    public function register(array|string $permissions, array $meta = []): void
    {
        foreach ((array) $permissions as $name) {
            $this->permissions[$name] = array_merge(
                ['name' => $name],
                ['module' => $meta['module'] ?? null],
                ['label' => $meta['label'] ?? $name],
                $meta
            );
        }
    }

    public function all(): array
    {
        return $this->permissions;
    }

    public function forModule(string $module): array
    {
        return array_filter($this->permissions, fn ($p) => ($p['module'] ?? null) === $module);
    }
}
