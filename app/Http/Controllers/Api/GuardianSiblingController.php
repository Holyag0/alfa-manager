<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Guardian;
use App\Models\SiblingGroup;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class GuardianSiblingController extends Controller
{
    /**
     * Gerenciar irmãos de um responsável
     */
    public function store(Request $request, Guardian $guardian): JsonResponse
    {
        $request->validate([
            'guardian_ids' => 'required|array|min:1',
            'guardian_ids.*' => 'exists:guardians,id',
            'group_name' => 'nullable|string|max:255'
        ]);

        // Se já tem grupo, usar o existente
        if ($guardian->sibling_group_id) {
            $group = $guardian->siblingGroup;
        } else {
            // Criar novo grupo
            $group = SiblingGroup::create([
                'name' => $request->group_name ?: "Grupo de {$guardian->name}",
                'description' => "Grupo de irmãos criado automaticamente",
                'is_active' => true
            ]);

            // Adicionar o responsável atual ao grupo
            $guardian->update(['sibling_group_id' => $group->id]);
        }

        // Adicionar os irmãos selecionados ao grupo
        Guardian::whereIn('id', $request->guardian_ids)
            ->update(['sibling_group_id' => $group->id]);

        $group->load(['guardians.students.enrollments.classroom']);

        return response()->json($group);
    }

    /**
     * Remover irmão do grupo
     */
    public function destroy(Guardian $guardian, Guardian $sibling): JsonResponse
    {
        // Verificar se estão no mesmo grupo
        if ($guardian->sibling_group_id !== $sibling->sibling_group_id) {
            return response()->json(['error' => 'Responsáveis não estão no mesmo grupo'], 400);
        }

        // Remover o irmão do grupo
        $sibling->update(['sibling_group_id' => null]);

        // Se o grupo ficar vazio, deletar
        $group = SiblingGroup::find($guardian->sibling_group_id);
        if ($group && $group->guardians()->count() <= 1) {
            $group->delete();
        }

        return response()->json(['message' => 'Irmão removido com sucesso']);
    }

    /**
     * Obter irmãos de um responsável
     */
    public function index(Guardian $guardian): JsonResponse
    {
        if (!$guardian->sibling_group_id) {
            return response()->json([]);
        }

        $siblings = $guardian->getActiveSiblings();

        return response()->json($siblings);
    }

    /**
     * Verificar se tem irmãos matriculados
     */
    public function hasActiveSiblings(Guardian $guardian): JsonResponse
    {
        $hasActiveSiblings = $guardian->hasActiveSiblings();
        $siblings = $hasActiveSiblings ? $guardian->getActiveSiblings() : collect();

        return response()->json([
            'has_active_siblings' => $hasActiveSiblings,
            'siblings_count' => $siblings->count(),
            'siblings' => $siblings
        ]);
    }
}
