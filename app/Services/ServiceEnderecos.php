<?php

namespace App\Services;
use App\Models\Endereco;

class ServiceEnderecos
{
    private $Enderecos;
    
    public function __construct(Endereco $Enderecos){
       $this->Enderecos = $Enderecos;

   }
    
    public function getEnderecos() {
        return $this->Enderecos;
    }

    public function EnderecosList() {
        return $this->getEnderecos()->pluck('nome','id');
    }

    public function all() {
        return $this->getEnderecos()->get()->paginate();
    }
    public function show($id) {
        return $this->getEnderecos()->find($id);
    }
    public function create($data) {
        return $this->getEnderecos()->create($data);
    }
    public function update($data, $id) {
        return $this->getEnderecos()->find($id)->update($data);
    }
    public function delete($id) {
        return $this->getEnderecos()->find($id)->delete();
    }

}