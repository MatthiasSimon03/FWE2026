<?php

namespace App\Models;

use CodeIgniter\Model;

class UserModel extends Model
{
    protected $table = 'users';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $allowedFields = ['name', 'email', 'password_hash', 'role', 'created_at', 'updated_at'];
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    public function getUserByEmail($email)
    {
        return $this->where('email', $email)->first();
    }

    public function createUser($name, $email, $password)
    {
        return $this->insert([
            'name' => $name,
            'email' => $email,
            'password_hash' => password_hash($password, PASSWORD_DEFAULT),
            'role' => 'user'
        ]);
    }

    public function verifyPassword($email, $password)
    {
        $user = $this->getUserByEmail($email);
        if (!$user) {
            return false;
        }
        return password_verify($password, $user['password_hash']) ? $user : false;
    }
}

