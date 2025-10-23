<?php

namespace App\Services;

use App\Models\Guardian;
use App\Models\Service;
use App\Models\Classroom;

class SiblingSuggestionService
{
    /**
     * Verificar se um responsável tem irmãos matriculados
     */
    public function hasActiveSiblings(Guardian $guardian): bool
    {
        return $guardian->hasActiveSiblings();
    }

    /**
     * Obter sugestões de serviços baseadas em irmãos
     */
    public function getServiceSuggestions(Guardian $guardian, $classroomId = null): array
    {
        $suggestions = [
            'has_siblings' => false,
            'siblings_count' => 0,
            'suggested_services' => [],
            'message' => ''
        ];

        if (!$this->hasActiveSiblings($guardian)) {
            $suggestions['message'] = 'Este responsável não possui irmãos matriculados.';
            return $suggestions;
        }

        $suggestions['has_siblings'] = true;
        $suggestions['siblings_count'] = $guardian->getActiveSiblings()->count();
        $suggestions['message'] = "Este responsável possui {$suggestions['siblings_count']} irmão(s) matriculado(s). Recomendamos serviços com desconto para irmãos.";

        // Buscar serviços "Com Irmão" para a turma específica
        if ($classroomId) {
            $suggestions['suggested_services'] = $this->getClassroomSiblingServices($classroomId);
        } else {
            $suggestions['suggested_services'] = $this->getGeneralSiblingServices();
        }

        return $suggestions;
    }

    /**
     * Obter serviços de irmãos para uma turma específica
     */
    private function getClassroomSiblingServices($classroomId): array
    {
        $classroom = Classroom::find($classroomId);
        if (!$classroom) {
            return [];
        }

        // Buscar serviços "Com Irmão" vinculados à turma
        $services = Service::whereHas('classroomServices', function ($query) use ($classroomId) {
            $query->where('classroom_id', $classroomId)
                  ->where('is_active', true);
        })
        ->where('name', 'like', '%Com Irmão%')
        ->where('status', 'active')
        ->get();

        return $services->map(function ($service) {
            return [
                'id' => $service->id,
                'name' => $service->name,
                'price' => $service->price,
                'formatted_price' => $service->formatted_price,
                'category' => $service->category,
                'description' => $service->description,
                'suggestion_reason' => 'Desconto para irmãos'
            ];
        })->toArray();
    }

    /**
     * Obter serviços gerais de irmãos
     */
    private function getGeneralSiblingServices(): array
    {
        $services = Service::where('name', 'like', '%Com Irmão%')
            ->where('status', 'active')
            ->get();

        return $services->map(function ($service) {
            return [
                'id' => $service->id,
                'name' => $service->name,
                'price' => $service->price,
                'formatted_price' => $service->formatted_price,
                'category' => $service->category,
                'description' => $service->description,
                'suggestion_reason' => 'Desconto para irmãos'
            ];
        })->toArray();
    }

    /**
     * Obter comparação de preços (sem/com irmão)
     */
    public function getPriceComparison($classroomId, $serviceType = 'monthly'): array
    {
        $classroom = Classroom::find($classroomId);
        if (!$classroom) {
            return [];
        }

        $services = Service::whereHas('classroomServices', function ($query) use ($classroomId) {
            $query->where('classroom_id', $classroomId)
                  ->where('is_active', true);
        })
        ->where('type', $serviceType)
        ->where('status', 'active')
        ->get();

        $comparison = [];
        
        foreach ($services as $service) {
            $baseName = str_replace([' - Sem Irmão', ' - Com Irmão'], '', $service->name);
            
            if (!isset($comparison[$baseName])) {
                $comparison[$baseName] = [
                    'base_name' => $baseName,
                    'without_sibling' => null,
                    'with_sibling' => null,
                    'savings' => 0
                ];
            }

            if (str_contains($service->name, 'Sem Irmão')) {
                $comparison[$baseName]['without_sibling'] = [
                    'id' => $service->id,
                    'name' => $service->name,
                    'price' => $service->price,
                    'formatted_price' => $service->formatted_price
                ];
            } elseif (str_contains($service->name, 'Com Irmão')) {
                $comparison[$baseName]['with_sibling'] = [
                    'id' => $service->id,
                    'name' => $service->name,
                    'price' => $service->price,
                    'formatted_price' => $service->formatted_price
                ];
            }
        }

        // Calcular economia
        foreach ($comparison as $key => $item) {
            if ($item['without_sibling'] && $item['with_sibling']) {
                $comparison[$key]['savings'] = $item['without_sibling']['price'] - $item['with_sibling']['price'];
            }
        }

        return array_values($comparison);
    }

    /**
     * Obter mensagem de sugestão personalizada
     */
    public function getSuggestionMessage(Guardian $guardian): string
    {
        if (!$this->hasActiveSiblings($guardian)) {
            return 'Este responsável não possui irmãos matriculados.';
        }

        $siblingsCount = $guardian->getActiveSiblings()->count();
        
        if ($siblingsCount === 1) {
            return 'Este responsável possui 1 irmão matriculado. Recomendamos serviços com desconto para irmãos.';
        }

        return "Este responsável possui {$siblingsCount} irmãos matriculados. Recomendamos serviços com desconto para irmãos.";
    }
}
