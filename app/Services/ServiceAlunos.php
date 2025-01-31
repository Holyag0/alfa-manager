<?php

namespace App\Services;
use App\Models\Alunos;

class ServiceAlunos
{
    private $Alunos;
    
    public function __construct(Alunos $Alunos){
       $this->Alunos = $Alunos;

   }
    
    public function getAlunos() {
        return $this->Alunos;
    }

    public function AlunossList() {
        return $this->getAlunos()->pluck('nome','id');
    }

    public function all() {
        return $this->getAlunos()->get()->paginate();
    }
    public function show($id) {
        return $this->getAlunos()->find($id);
    }
    public function create($data) {
        return $this->getAlunos()->create($data);
    }
    public function update($data, $id) {
        return $this->getAlunos()->find($id)->update($data);
    }
    public function delete($id) {
        return $this->getAlunos()->find($id)->delete();
    }

}