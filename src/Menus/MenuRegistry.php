<?php

namespace Mercator\Core\Menus;

use Illuminate\Support\Facades\Log;

class MenuRegistry
{
    /**
     * Structure du menu :
     * [
     *   'section-id' => [
     *       'label' => 'Infrastructure',
     *       'items' => [
     *          [
     *            'id'         => 'firewall',
     *            'label'      => 'Pare-feu',
     *            'route'      => 'mercator.firewall.index',
     *            'icon'       => 'shield',
     *            'permission' => 'firewall.view'
     *          ],
     *       ]
     *   ]
     * ]
     */
    protected array $sections = [];

    public function addSection(string $id, string $label): void
    {
        if (!isset($this->sections[$id])) {
            $this->sections[$id] = [
                'label' => $label,
                'items' => [],
            ];
        }
    }

    public function addItem(string $sectionId, array $item): void
    {
        // Log::debug('MenuRegistry::addItem', ['sectionId' => $sectionId, 'item' => $item]);
        if (!isset($this->sections[$sectionId])) {
            $this->addSection($sectionId, ucfirst($sectionId));
        }

        $this->sections[$sectionId]['items'][] = $item;
    }

    public function get(): array
    {
        return $this->sections;
    }

    public function getItems(string $sectionId): ?array
    {
        // Log::debug('MenuRegistry::getItems', ['sectionId' => $sectionId]);
        return $this->sections[$sectionId]['items'] ?? null;
    }
}
