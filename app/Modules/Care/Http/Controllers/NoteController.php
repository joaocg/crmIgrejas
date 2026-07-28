<?php

declare(strict_types=1);

namespace App\Modules\Care\Http\Controllers;

use App\Models\Note;
use App\Modules\Care\Actions\CreateNoteAction;
use App\Modules\Care\Actions\DeleteNoteAction;
use App\Modules\Care\Actions\ListNotesAction;
use App\Modules\Care\Actions\UpdateNoteAction;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

final class NoteController
{
    public function index(Request $request, ListNotesAction $action): JsonResponse
    {
        return response()->json($action->execute((int) $request->user()->tenant_id));
    }

    public function store(Request $request, CreateNoteAction $action): JsonResponse
    {
        $validated = $request->validate([
            'person_id' => [
                'nullable',
                'integer',
                Rule::exists('persons', 'id')->where(fn ($query) => $query->where('tenant_id', $request->user()->tenant_id)),
            ],
            'family_id' => [
                'nullable',
                'integer',
                Rule::exists('families', 'id')->where(fn ($query) => $query->where('tenant_id', $request->user()->tenant_id)),
            ],
            'title' => ['nullable', 'string', 'max:255'],
            'body' => ['nullable', 'string'],
            'type' => ['nullable', 'string', 'max:32'],
            'info' => ['nullable', 'string', 'max:255'],
            'is_private' => ['nullable', 'boolean'],
            'edited_by_user_id' => [
                'nullable',
                'integer',
                Rule::exists('users', 'id')->where(fn ($query) => $query->where('tenant_id', $request->user()->tenant_id)),
            ],
            'edited_at' => ['nullable', 'date'],
        ]);

        $validated['edited_by_user_id'] ??= $request->user()->id;
        $validated['edited_at'] ??= now();

        return response()->json($action->execute((int) $request->user()->tenant_id, $validated), 201);
    }

    public function show(Request $request, Note $note): JsonResponse
    {
        $this->authorizeTenantAccess($request, $note);

        return response()->json($note->load(['person', 'family', 'editedBy']));
    }

    public function update(Request $request, Note $note, UpdateNoteAction $action): JsonResponse
    {
        $this->authorizeTenantAccess($request, $note);

        $validated = $request->validate([
            'person_id' => [
                'sometimes',
                'nullable',
                'integer',
                Rule::exists('persons', 'id')->where(fn ($query) => $query->where('tenant_id', $request->user()->tenant_id)),
            ],
            'family_id' => [
                'sometimes',
                'nullable',
                'integer',
                Rule::exists('families', 'id')->where(fn ($query) => $query->where('tenant_id', $request->user()->tenant_id)),
            ],
            'title' => ['sometimes', 'nullable', 'string', 'max:255'],
            'body' => ['sometimes', 'nullable', 'string'],
            'type' => ['sometimes', 'nullable', 'string', 'max:32'],
            'info' => ['sometimes', 'nullable', 'string', 'max:255'],
            'is_private' => ['sometimes', 'boolean'],
            'edited_by_user_id' => [
                'sometimes',
                'nullable',
                'integer',
                Rule::exists('users', 'id')->where(fn ($query) => $query->where('tenant_id', $request->user()->tenant_id)),
            ],
            'edited_at' => ['sometimes', 'nullable', 'date'],
        ]);

        return response()->json($action->execute($note, $validated));
    }

    public function destroy(Request $request, Note $note, DeleteNoteAction $action): JsonResponse
    {
        $this->authorizeTenantAccess($request, $note);

        $action->execute($note);

        return response()->json([], 204);
    }

    private function authorizeTenantAccess(Request $request, Note $note): void
    {
        abort_unless((int) $note->tenant_id === (int) $request->user()->tenant_id, 404);
    }
}
