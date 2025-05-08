<?php

namespace App\Services;
use App\Models\User;
use App\Actions\Fortify\CreateNewUser;

class ServiceUsers
{
    private User $user;
    private CreateNewUser $createNewUser;
    
    public function __construct(User $user, CreateNewUser $createNewUser)
    {
        $this->user = $user;
        $this->createNewUser = $createNewUser;
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
        // $data['password'] = bcrypt($data['password']);
        // return $this->getUsers()->create($data);
        return $this->createNewUser->create($data);
    }
    public function update($data, $id) {
        return $this->getUsers()->findOrFail($id)->update($data);
    }
    public function delete($id) {
        return $this->getUsers()->findOrFail($id)->delete();
    }

}