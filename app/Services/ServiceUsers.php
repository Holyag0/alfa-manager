<?php

namespace App\Services;

use App\Models\User;
use App\Actions\Fortify\CreateNewUser as CreateByJetstream;
use App\Actions\Fortify\UpdateUserProfileInformation as UpdateByJetStream;
use App\Actions\Jetstream\DeleteUser as DeleteByJetstream;
use Illuminate\Support\Facades\DB;
use Illuminate\Pagination\LengthAwarePaginator;

class ServiceUsers
{
    private User $user;
    private CreateByJetstream $createByJetstream;
    private UpdateByJetStream $updateJetstream;
    private DeleteByJetstream $deleteByJetstream;

    public function __construct(
        User $user, 
        CreateByJetstream $createByJetstream,
        UpdateByJetStream $updateJetstream,
        DeleteByJetstream $deleteByJetstream
    ) {
        $this->user = $user;
        $this->createByJetstream = $createByJetstream;
        $this->updateJetstream = $updateJetstream;
        $this->deleteByJetstream = $deleteByJetstream;
    }

    public function getUser(): User {
        return $this->user;
    }

    public function getSession(int $id): \Illuminate\Support\Collection{
        return DB::table('sessions')
            ->where('user_id', $id)
            ->get();
    }

    public function usersList(): \Illuminate\Support\Collection{
        return $this->getUser()->pluck('nome', 'id');
    }

    public function all(int $perPage = 15): LengthAwarePaginator {
        return $this->getUser()
            ->orderBy('id', 'desc')
            ->paginate($perPage);
    }

    public function show(int $id): ?User{
        return $this->getUser()->find($id);
    }

    public function create(array $data): User{
        return $this->createByJetstream->create($data);
    }

    public function update(User $user, array $data): void{
        $this->updateJetstream->update($user, $data);
    }

    public function delete(int $id): bool
    {
        $user = $this->user->find($id);

        if (!$user) {
            throw new \Exception("Usuário não encontrado.");
        }

        try {
            $this->deleteByJetstream->delete($user);
            return true;
        } catch (\Throwable $e) {
            return false;
        }
    }
}