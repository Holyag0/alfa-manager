<?php

namespace App\Services;
use App\Models\User;

class ServiceUsers
{
    private $user;
    
    public function __construct(User $users){
       $this->user = $users;

   }
    
    public function getUsers() {
        return $this->user;
    }

    public function usersList() {
        return $this->getUsers()->pluck('nome','id');
    }

    public function all() {
        return $this->getUsers()->get()->paginate();
    }
    public function show($id) {
        return $this->getUsers()->find($id);
    }
    public function create($data) {
        $data['password'] = bcrypt($data['password']);
        return $this->getUsers()->create($data);
    }
    public function update($data, $id) {
        return $this->getUsers()->find($id)->update($data);
    }
    public function delete($id) {
        return $this->getUsers()->find($id)->delete();
    }

}