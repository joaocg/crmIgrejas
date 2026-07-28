<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

final class NavigationController extends Controller
{
    public function __invoke(): JsonResponse
    {
        return response()->json([
            'sections' => [
                [
                    'key' => 'main',
                    'label' => 'Menu principal',
                    'items' => [
                        ['key' => 'dashboard', 'route' => '/dashboard', 'label' => 'Painel', 'icon' => '⌂'],
                        ['key' => 'users', 'route' => '/users', 'label' => 'Usuários', 'icon' => '◫', 'meta' => 'CRUD'],
                        ['key' => 'people', 'route' => '/people', 'label' => 'Pessoas', 'icon' => '◉', 'meta' => 'CRUD'],
                        ['key' => 'families', 'route' => '/families', 'label' => 'Famílias', 'icon' => '◔', 'meta' => 'CRUD'],
                        ['key' => 'groups', 'route' => '/groups', 'label' => 'Grupos', 'icon' => '◑', 'meta' => 'CRUD'],
                        ['key' => 'events', 'route' => '/events', 'label' => 'Eventos', 'icon' => '◒', 'meta' => 'CRUD'],
                    ],
                ],
                [
                    'key' => 'tools',
                    'label' => 'Ferramentas',
                    'items' => [
                        ['key' => 'communications', 'route' => '/communications', 'label' => 'Comunicação', 'icon' => '✉'],
                        ['key' => 'care', 'route' => '/care', 'label' => 'Intercessão', 'icon' => '✚'],
                        ['key' => 'repertoire', 'route' => '/repertoire', 'label' => 'Repertório', 'icon' => '♫'],
                        ['key' => 'manuals', 'route' => '/manuals', 'label' => 'Manuais', 'icon' => '▣'],
                        ['key' => 'whatsapp', 'route' => '/settings/integrations/whatsapp', 'label' => 'WhatsApp', 'icon' => '☏'],
                    ],
                ],
            ],
        ]);
    }
}
