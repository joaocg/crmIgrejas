<?php

declare(strict_types=1);

namespace App\Modules\Users\Http\Controllers;

use App\Models\User;
use App\Modules\Users\Actions\CreateUserAction;
use App\Modules\Users\Actions\DeleteUserAction;
use App\Modules\Users\Actions\ListUsersAction;
use App\Modules\Users\Actions\UpdateUserAction;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class UserController
{
    public function index(ListUsersAction $action): JsonResponse
    {
        return response()->json($action->execute());
    }

    public function store(Request $request, CreateUserAction $action): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8'],
            'tenant_id' => ['nullable', 'integer'],
            'role_id' => ['nullable', 'integer'],
            'locale' => ['nullable', 'string', 'max:10'],
            'active' => ['nullable', 'boolean'],
        ]);

        return response()->json($action->execute($validated), 201);
    }

    public function show(User $user): JsonResponse
    {
        return response()->json($user);
    }

    public function update(Request $request, User $user, UpdateUserAction $action): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'email' => ['sometimes', 'email', 'max:255', 'unique:users,email,'.$user->id],
            'password' => ['sometimes', 'string', 'min:8'],
            'tenant_id' => ['sometimes', 'nullable', 'integer'],
            'role_id' => ['sometimes', 'nullable', 'integer'],
            'locale' => ['sometimes', 'nullable', 'string', 'max:10'],
            'active' => ['sometimes', 'boolean'],
        ]);

        return response()->json($action->execute($user, $validated));
    }

    public function destroy(User $user, DeleteUserAction $action): JsonResponse
    {
        $action->execute($user);

        return response()->json([], 204);
    }
}
