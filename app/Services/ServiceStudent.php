<?php

namespace App\Services;

use App\Models\Student;

class ServiceStudent
{
    public function create(array $data)
    {
        $photoPath = null;
        if (isset($data['photo']) && $data['photo'] instanceof \Illuminate\Http\UploadedFile) {
            $photoPath = $data['photo']->store('students', 'public');
            $data['photo'] = $photoPath;
        }
        $student = Student::create($data);

        // Contatos (opcional)
        if (!empty($data['contacts']) && is_array($data['contacts'])) {
            foreach ($data['contacts'] as $contact) {
                if (!empty(array_filter($contact))) {
                    $student->contacts()->create($contact);
                }
            }
        }

        return $student;
    }

    public function update(Student $student, array $data): Student
    {
        // Processar upload de foto apenas se houver novo arquivo
        if (isset($data['photo']) && $data['photo'] instanceof \Illuminate\Http\UploadedFile) {
            // Remover foto antiga se existir
            if ($student->photo && \Illuminate\Support\Facades\Storage::disk('public')->exists($student->photo)) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($student->photo);
            }
            
            $photoPath = $data['photo']->store('students', 'public');
            $data['photo'] = $photoPath;
        } elseif (isset($data['photo']) && $data['photo'] === 'DELETE') {
            // Se o frontend enviar 'DELETE', remover a foto existente
            if ($student->photo && \Illuminate\Support\Facades\Storage::disk('public')->exists($student->photo)) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($student->photo);
            }
            $data['photo'] = null;
        } else {
            // Se não houver novo arquivo e não for para deletar, remover o campo photo do array
            // para não sobrescrever a foto existente
            unset($data['photo']);
        }
        
        $student->update($data);
        return $student;
    }

    public function delete(Student $student): bool
    {
        return $student->delete();
    }

    public function find($id): ?Student
    {
        return Student::find($id);
    }

    public function search($term = null)
    {
        return Student::query()
            ->when($term, fn($q) => $q->where('name', 'like', "%$term%"))
            ->limit(10)
            ->get();
    }

    /**
     * Obter irmãos de um aluno
     */
    public function getSiblings(Student $student)
    {
        return $student->getSiblings();
    }

    /**
     * Adicionar irmão ao aluno
     * Cria ou atualiza grupos de irmãos através dos responsáveis
     * 
     * REGRA 3: Valida se ambos os alunos têm responsáveis
     */
    public function addSibling(Student $student, Student $sibling)
    {
        // Buscar responsáveis do aluno atual
        $studentGuardians = $student->guardians;
        
        // Buscar responsáveis do irmão
        $siblingGuardians = $sibling->guardians;

        // REGRA 3: Validar que ambos têm responsáveis
        if ($studentGuardians->isEmpty() || $siblingGuardians->isEmpty()) {
            throw new \Exception('Ambos os alunos precisam ter pelo menos um responsável para serem vinculados como irmãos.');
        }

        // REGRA 4: Verificar se já são irmãos
        if ($this->areSiblings($student, $sibling)) {
            throw new \Exception(
                "Não é possível adicionar o vínculo de irmão. " .
                "Os alunos '{$student->name}' e '{$sibling->name}' já estão vinculados como irmãos. " .
                "Esta validação evita duplicação de vínculos no sistema."
            );
        }

        // Usar o primeiro responsável do aluno atual como referência
        $mainGuardian = $studentGuardians->first();
        
        // Se o responsável principal já tem um grupo, usar esse grupo
        if ($mainGuardian->sibling_group_id) {
            $group = $mainGuardian->siblingGroup;
        } else {
            // Criar novo grupo de irmãos
            $group = \App\Models\SiblingGroup::create([
                'name' => "Grupo de {$student->name} e {$sibling->name}",
                'description' => "Grupo de irmãos criado automaticamente",
                'is_active' => true
            ]);

            // Adicionar todos os responsáveis do aluno atual ao grupo
            foreach ($studentGuardians as $guardian) {
                $guardian->update(['sibling_group_id' => $group->id]);
            }
        }

        // Adicionar todos os responsáveis do irmão ao mesmo grupo
        foreach ($siblingGuardians as $guardian) {
            $guardian->update(['sibling_group_id' => $group->id]);
        }

        return $group;
    }

    /**
     * Verificar se dois alunos já são irmãos
     * 
     * REGRA 4: Validação de duplicação
     */
    protected function areSiblings(Student $student, Student $sibling): bool
    {
        // Buscar grupos de irmãos dos responsáveis do aluno
        $studentGroupIds = $student->guardians()
            ->whereNotNull('sibling_group_id')
            ->pluck('sibling_group_id')
            ->unique();

        // Buscar grupos de irmãos dos responsáveis do irmão
        $siblingGroupIds = $sibling->guardians()
            ->whereNotNull('sibling_group_id')
            ->pluck('sibling_group_id')
            ->unique();

        // Se há grupos compartilhados, já são irmãos
        return $studentGroupIds->intersect($siblingGroupIds)->isNotEmpty();
    }

    /**
     * Remover vínculo de irmão
     * 
     * REGRA 1: Só pode remover irmão se a última matrícula criada estiver com:
     * - status: completed
     * - process: completa, desistencia, transferencia
     * 
     * REGRA 5: Não permitir remover irmão se ambos os alunos tiverem matrículas ativas no mesmo ano letivo
     */
    public function removeSibling(Student $student, Student $sibling)
    {
        // Buscar grupos de irmãos compartilhados
        $studentGroupIds = $student->guardians()
            ->whereNotNull('sibling_group_id')
            ->pluck('sibling_group_id')
            ->unique();

        $siblingGroupIds = $sibling->guardians()
            ->whereNotNull('sibling_group_id')
            ->pluck('sibling_group_id')
            ->unique();

        $sharedGroups = $studentGroupIds->intersect($siblingGroupIds);

        if ($sharedGroups->isEmpty()) {
            throw new \Exception('Os alunos não estão vinculados como irmãos.');
        }

        // REGRA 5: Validar matrículas ativas no mesmo ano letivo
        $this->validateActiveEnrollmentsInSameYear($student, $sibling);

        // REGRA 1: Validar status da última matrícula do aluno
        $this->validateLastEnrollmentStatus($student, 'aluno');
        
        // REGRA 1: Validar status da última matrícula do irmão
        $this->validateLastEnrollmentStatus($sibling, 'irmão');

        // Remover responsáveis do irmão dos grupos compartilhados
        foreach ($sibling->guardians as $guardian) {
            if ($sharedGroups->contains($guardian->sibling_group_id)) {
                $guardian->update(['sibling_group_id' => null]);
            }
        }

        // Verificar se algum grupo ficou vazio e deletar se necessário
        foreach ($sharedGroups as $groupId) {
            $group = \App\Models\SiblingGroup::find($groupId);
            if ($group && $group->guardians()->count() <= 1) {
                $group->delete();
            }
        }

        return true;
    }

    /**
     * Validar status da última matrícula criada
     * 
     * @param Student $student
     * @param string $studentLabel Label para mensagem de erro (ex: 'aluno', 'irmão')
     * @throws \Exception
     */
    protected function validateLastEnrollmentStatus(Student $student, string $studentLabel = 'aluno')
    {
        // Buscar a última matrícula criada (ordenada por created_at)
        $lastEnrollment = \App\Models\Enrollment::where('student_id', $student->id)
            ->orderBy('created_at', 'desc')
            ->first();

        // Se não tem matrícula, permite remover (aluno novo sem matrícula)
        if (!$lastEnrollment) {
            return;
        }

        // Status permitidos para remoção
        $allowedStatuses = ['completed'];
        
        // Process permitidos para remoção
        $allowedProcesses = ['completa', 'desistencia', 'transferencia'];

        // Verificar se a última matrícula atende aos critérios
        $hasAllowedStatus = in_array($lastEnrollment->status, $allowedStatuses);
        $hasAllowedProcess = in_array($lastEnrollment->process, $allowedProcesses);

        if (!$hasAllowedStatus || !$hasAllowedProcess) {
            $statusLabel = $this->getEnrollmentStatusLabel($lastEnrollment->status);
            $processLabel = $this->getEnrollmentProcessLabel($lastEnrollment->process);
            $studentName = $student->name;
            
            $message = "Não é possível remover o vínculo de irmão.\n\n";
            $message .= "📋 REGRA DE NEGÓCIO:\n";
            $message .= "Apenas matrículas finalizadas permitem a remoção do vínculo de irmão.\n\n";
            $message .= "❌ Situação atual do {$studentLabel} '{$studentName}':\n";
            $message .= "   • Status da última matrícula: {$statusLabel}\n";
            $message .= "   • Processo da última matrícula: {$processLabel}\n\n";
            $message .= "✅ Para remover o vínculo, a última matrícula deve estar:\n";
            $message .= "   • Status: Concluída (completed)\n";
            $message .= "   • Processo: Completa, Desistência ou Transferência\n\n";
            $message .= "💡 Esta regra protege:\n";
            $message .= "   • Descontos de irmão aplicados em mensalidades ativas\n";
            $message .= "   • Integridade dos contratos financeiros\n";
            $message .= "   • Consistência dos dados do sistema";
            
            throw new \Exception($message);
        }
    }

    /**
     * Obter label do status da matrícula
     */
    protected function getEnrollmentStatusLabel(string $status): string
    {
        $labels = [
            'active' => 'Ativa',
            'pending' => 'Pendente',
            'cancelled' => 'Cancelada',
            'inactive' => 'Inativa',
            'completed' => 'Concluída',
        ];

        return $labels[$status] ?? $status;
    }

    /**
     * Obter label do processo da matrícula
     */
    protected function getEnrollmentProcessLabel(string $process): string
    {
        $labels = [
            'reserva' => 'Reserva',
            'aguardando_pagamento' => 'Aguardando Pagamento',
            'aguardando_documentos' => 'Aguardando Documentos',
            'desistencia' => 'Desistência',
            'transferencia' => 'Transferência',
            'renovacao' => 'Renovação',
            'completa' => 'Completa',
        ];

        return $labels[$process] ?? $process;
    }

    /**
     * Validar se ambos os alunos têm matrículas ativas no mesmo ano letivo
     * 
     * REGRA 5: Não permitir remover irmão se ambos os alunos tiverem matrículas ativas no mesmo ano letivo
     * 
     * @param Student $student
     * @param Student $sibling
     * @throws \Exception
     */
    protected function validateActiveEnrollmentsInSameYear(Student $student, Student $sibling)
    {
        // Buscar matrículas ativas do aluno
        $studentActiveEnrollments = \App\Models\Enrollment::where('student_id', $student->id)
            ->where('status', 'active')
            ->get();

        // Buscar matrículas ativas do irmão
        $siblingActiveEnrollments = \App\Models\Enrollment::where('student_id', $sibling->id)
            ->where('status', 'active')
            ->get();

        // Se nenhum dos dois tem matrícula ativa, permite remover
        if ($studentActiveEnrollments->isEmpty() && $siblingActiveEnrollments->isEmpty()) {
            return;
        }

        // Buscar anos letivos das matrículas ativas
        $studentYears = $studentActiveEnrollments->pluck('academic_year')->unique();
        $siblingYears = $siblingActiveEnrollments->pluck('academic_year')->unique();

        // Verificar se há anos letivos em comum
        $commonYears = $studentYears->intersect($siblingYears);

        if ($commonYears->isNotEmpty()) {
            $yearsList = $commonYears->implode(', ');
            $studentName = $student->name;
            $siblingName = $sibling->name;
            
            throw new \Exception(
                "Não é possível remover o vínculo de irmão. " .
                "Os alunos '{$studentName}' e '{$siblingName}' possuem matrículas ativas no(s) mesmo(s) ano(s) letivo(s): {$yearsList}. " .
                "Para remover o vínculo, é necessário encerrar todas as matrículas ativas dos alunos no mesmo ano letivo. " .
                "Isso protege os descontos de irmão aplicados durante o período letivo ativo."
            );
        }
    }
} 