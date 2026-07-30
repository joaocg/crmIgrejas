<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Policies\GroupPolicy;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class NavigationController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $permissions = $request->user()?->role?->permissions ?? ['*' => true];

        $sections = [
            [
                'key' => 'main',
                'labelKey' => 'navigation.main',
                'items' => [
                    ['key' => 'dashboard', 'route' => '/dashboard', 'labelKey' => 'navigation.dashboard', 'icon' => '⌂'],
                    ['key' => 'users', 'route' => '/users', 'labelKey' => 'navigation.users', 'icon' => '◫', 'meta' => 'CRUD', 'ability' => 'navigation.users'],
                    ['key' => 'people', 'route' => '/people', 'labelKey' => 'navigation.people', 'icon' => '◉', 'meta' => 'CRUD', 'ability' => 'navigation.people'],
                    ['key' => 'families', 'route' => '/families', 'labelKey' => 'navigation.families', 'icon' => '◔', 'meta' => 'CRUD', 'ability' => 'navigation.families'],
                    ['key' => 'groups', 'route' => '/groups', 'labelKey' => 'navigation.groups', 'icon' => '◑', 'meta' => 'CRUD', 'ability' => GroupPolicy::VIEW_ALL_ABILITY],
                    ['key' => 'events', 'route' => '/events', 'labelKey' => 'navigation.events', 'icon' => '◒', 'meta' => 'CRUD', 'ability' => 'navigation.events'],
                ],
            ],
            [
                'key' => 'tools',
                'labelKey' => 'navigation.tools',
                'items' => [
                    ['key' => 'communications', 'route' => '/communications', 'labelKey' => 'navigation.communications', 'icon' => '✉', 'ability' => 'navigation.communications'],
                    ['key' => 'care', 'route' => '/care', 'labelKey' => 'navigation.care', 'icon' => '✚', 'ability' => 'navigation.care'],
                    ['key' => 'finance', 'route' => '/finance', 'labelKey' => 'navigation.finance', 'icon' => '¤', 'ability' => 'navigation.finance'],
                    ['key' => 'calendar', 'route' => '/calendar', 'labelKey' => 'navigation.calendar', 'icon' => '◷', 'ability' => 'navigation.calendar'],
                    ['key' => 'kiosk', 'route' => '/kiosk', 'labelKey' => 'navigation.kiosk', 'icon' => '▣', 'ability' => 'navigation.kiosk'],
                    ['key' => 'repertoire', 'route' => '/repertoire', 'labelKey' => 'navigation.repertoire', 'icon' => '♫', 'ability' => 'navigation.repertoire'],
                    ['key' => 'manuals', 'route' => '/manuals', 'labelKey' => 'navigation.manuals', 'icon' => '▣', 'ability' => 'navigation.manuals'],
                    ['key' => 'whatsapp', 'route' => '/settings/integrations/whatsapp', 'labelKey' => 'navigation.whatsapp', 'icon' => '☏', 'ability' => 'navigation.whatsapp'],
                ],
            ],
        ];

        $filteredSections = array_values(array_filter(array_map(function (array $section) use ($permissions): ?array {
            $items = array_values(array_filter($section['items'], function (array $item) use ($permissions): bool {
                if (($item['ability'] ?? null) === null) {
                    return true;
                }

                if (($permissions['*'] ?? false) === true) {
                    return true;
                }

                return ($permissions[$item['ability']] ?? false) === true;
            }));

            if ($items === []) {
                return null;
            }

            $section['items'] = $items;

            return $section;
        }, $sections)));

        return response()->json([
            'sections' => $filteredSections,
        ]);
    }
}
