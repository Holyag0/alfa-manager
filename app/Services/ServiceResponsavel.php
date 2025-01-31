<?php

namespace App\Services;
use App\Models\Responsavel;

class ServiceResponsavel
{
    private $responsavel;
    
    public function __construct(Responsavel $responsavel){
       $this->responsavel = $responsavel;

   }
    
    public function getResponsavel() {
        return $this->responsavel;
    }

    public function responsavelList() {
        return $this->getResponsavel()->pluck('nome','id');
    }

    public function all() {
        return $this->getResponsavel()->get()->paginate();
    }
    public function show($id) {
        return $this->getResponsavel()->find($id);
    }
    public function create($data) {
        return $this->getResponsavel()->create($data);
    }
    public function update($data, $id) {
        return $this->getResponsavel()->find($id)->update($data);
    }
    public function delete($id) {
        return $this->getResponsavel()->find($id)->delete();
    }

}