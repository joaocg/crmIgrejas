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

final class NoteController
{
    public function index(ListNotesAction $action): JsonResponse
    {
        return response()->json($action->execute());
    }

    public function store(Request $request, CreateNoteAction $action): JsonResponse
    {
        $validated = $request->validate([
            'tenant_id' => ['required', 'integer', 'exists:tenants,id'],
            'person_id' => ['nullable', 'integer', 'exists:persons,id'],
            'family_id' => ['nullable', 'integer', 'exists:families,id'],
            'title' => ['nullable', 'string', 'max:255'],
            'body' => ['nullable', 'string'],
            'type' => ['nullable', 'string', 'max:32'],
            'info' => ['nullable', 'string', 'max:255'],
            'is_private' => ['nullable', 'boolean'],
            'edited_by_user_id' => ['nullable', 'integer', 'exists:users,id'],
            'edited_at' => ['nullable', 'date'],
        ]);

        return response()->json($action->execute($validated), 201);
    }

    public function show(Note $note): JsonResponse
    {
        return response()->json($note->load(['person', 'family', 'editedBy']));
    }

    public function update(Request $request, Note $note, UpdateNoteAction $action): JsonResponse
    {
        $validated = $request->validate([
            'tenant_id' => ['sometimes', 'integer', 'exists:tenants,id'],
            'person_id' => ['sometimes', 'nullable', 'integer', 'exists:persons,id'],
            'family_id' => ['sometimes', 'nullable', 'integer', 'exists:families,id'],
            'title' => ['sometimes', 'nullable', 'string', 'max:255'],
            'body' => ['sometimes', 'nullable', 'string'],
            'type' => ['sometimes', 'nullable', 'string', 'max:32'],
            'info' => ['sometimes', 'nullable', 'string', 'max:255'],
            'is_private' => ['sometimes', 'boolean'],
            'edited_by_user_id' => ['sometimes', 'nullable', 'integer', 'exists:users,id'],
            'edited_at' => ['sometimes', 'nullable', 'date'],
        ]);

        return response()->json($action->execute($note, $validated));
    }

    public function destroy(Note $note, DeleteNoteAction $action): JsonResponse
    {
        $action->execute($note);

        return response()->json([], 204);
    }
}
