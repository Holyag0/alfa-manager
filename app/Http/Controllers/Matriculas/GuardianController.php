<?php

namespace App\Http\Controllers\Matriculas;

use App\Http\Controllers\Controller;
use App\Http\Requests\GuardianRequest;
use App\Models\Guardian;
use App\Services\ServiceGuardian;
use Illuminate\Http\Request;
use Inertia\Inertia;

class GuardianController extends Controller
{
    protected ServiceGuardian $service;

    public function __construct(ServiceGuardian $service)
    {
        $this->service = $service;
    }

    public function index(Request $request)
    {
        $guardians = $this->service->search($request->input('q'));
        return response()->json($guardians);
    }

    public function edit($id)
    {
        $guardian = $this->service->find($id);
        if (!$guardian) {
            return redirect()->back()->withErrors(['error' => 'Responsável não encontrado']);
        }
        
        return Inertia::render('Guardian/Edit', [
            'guardian' => $guardian
        ]);
    }

    public function store(GuardianRequest $request)
    {
        $data = $request->validated();
        $guardian = $this->service->create($data);
        $guardian = $this->service->find($guardian->id);

        return redirect()->back()->with([
            'success' => 'Responsável cadastrado com sucesso!',
            'guardian' => $guardian ? $guardian->toArray() : null
        ]);
    }

    public function show($id)
    {
        $guardian = $this->service->find($id);
        if (!$guardian) return response()->json(['error' => 'Not found'], 404);
        return response()->json($guardian);
    }

    public function update(GuardianRequest $request, $id)
    {
        $guardian = $this->service->find($id);
        if (!$guardian) {
            return redirect()->back()->withErrors(['error' => 'Responsável não encontrado']);
        }
        
        $this->service->update($guardian, $request->validated());
        return redirect()->route('guardian.edit', $id)->with('success', 'Responsável atualizado com sucesso!');
    }

    public function destroy($id)
    {
        $guardian = $this->service->find($id);
        if (!$guardian) return response()->json(['error' => 'Not found'], 404);
        $this->service->delete($guardian);
        return response()->json(['success' => true]);
    }

    public function autocomplete(Request $request)
    {
        $results = $this->service->search($request->input('q'));
        return response()->json($results);
    }

    /**
     * Buscar responsáveis não vinculados a um aluno específico
     */
    public function searchNotLinked(Request $request, $studentId)
    {
        $term = $request->input('q');
        $guardians = $this->service->searchNotLinkedToStudent($studentId, $term);
        return response()->json($guardians); // Este pode continuar JSON para autocomplete
    }

    /**
     * Vincular responsável a um aluno
     */
    public function attachToStudent(Request $request, $studentId)
    {
        $request->validate([
            'guardian_id' => 'required|exists:guardians,id',
        ]);

        // try {
            $guardian = $this->service->attachToStudent(
                $request->guardian_id,
                $studentId
            );
            
            return redirect()->back()->with('success', 'Responsável vinculado ao aluno com sucesso!');
        // } catch (\Exception $e) {
        //     return redirect()->back()->withErrors(['error' => $e->getMessage()]);
        // }
    }

    /**
     * Desvincular responsável de um aluno
     */
    public function detachFromStudent($studentId, $guardianId)
    {
        // try {
            $this->service->detachFromStudent($guardianId, $studentId);
            return redirect()->back()->with('success', 'Responsável desvinculado do aluno com sucesso!');
        // } catch (\Exception $e) {
        //     return redirect()->back()->withErrors(['error' => $e->getMessage());
        // }
    }
} 