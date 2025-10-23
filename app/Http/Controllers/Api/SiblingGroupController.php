<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SiblingGroup;
use App\Models\Guardian;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class SiblingGroupController extends Controller
{
    /**
     * Listar grupos de irmãos
     */
    public function index(): JsonResponse
    {
        $groups = SiblingGroup::with(['guardians.students.enrollments.classroom'])
            ->active()
            ->get();

        return response()->json($groups);
    }

    /**
     * Mostrar grupo específico
     */
    public function show(SiblingGroup $siblingGroup): JsonResponse
    {
        $siblingGroup->load(['guardians.students.enrollments.classroom']);
        
        return response()->json($siblingGroup);
    }

    /**
     * Criar novo grupo
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'guardian_ids' => 'required|array|min:1',
            'guardian_ids.*' => 'exists:guardians,id'
        ]);

        $group = SiblingGroup::create([
            'name' => $request->name,
            'description' => $request->description,
            'is_active' => true
        ]);

        // Vincular responsáveis ao grupo
        Guardian::whereIn('id', $request->guardian_ids)
            ->update(['sibling_group_id' => $group->id]);

        $group->load(['guardians.students.enrollments.classroom']);

        return response()->json($group, 201);
    }

    /**
     * Atualizar grupo
     */
    public function update(Request $request, SiblingGroup $siblingGroup): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'guardian_ids' => 'required|array|min:1',
            'guardian_ids.*' => 'exists:guardians,id'
        ]);

        $siblingGroup->update([
            'name' => $request->name,
            'description' => $request->description
        ]);

        // Remover vínculos antigos
        Guardian::where('sibling_group_id', $siblingGroup->id)
            ->update(['sibling_group_id' => null]);

        // Adicionar novos vínculos
        Guardian::whereIn('id', $request->guardian_ids)
            ->update(['sibling_group_id' => $siblingGroup->id]);

        $siblingGroup->load(['guardians.students.enrollments.classroom']);

        return response()->json($siblingGroup);
    }

    /**
     * Deletar grupo
     */
    public function destroy(SiblingGroup $siblingGroup): JsonResponse
    {
        // Remover vínculos
        Guardian::where('sibling_group_id', $siblingGroup->id)
            ->update(['sibling_group_id' => null]);

        $siblingGroup->delete();

        return response()->json(['message' => 'Grupo deletado com sucesso']);
    }
}
