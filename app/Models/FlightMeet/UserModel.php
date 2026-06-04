<?php

namespace app\Models\FlightMeet;

use CodeIgniter\Model;

class UserModel extends Model
{
    protected $table = 'fm_users';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $allowedFields = [
        'username', 'email', 'password_hash', 'role', 'experience_level', 'created_at', 'updated_at'
    ];
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    public function getUserByEmail(string $email)
    {
        return $this->where(['email' => $email])->first();
    }

    public function createUser(string $username, string $email, string $password, string $experience_level)
    {
        return $this->insert([
            'username' => $username,
            'email' => $email,
            'password_hash' => password_hash($password, PASSWORD_DEFAULT),
            'role' => 'user',
            'experience_level' => $experience_level
        ]);
    }

    public function verifyPassword(string $email, string $password)
    {
        $user = $this->getUserByEmail($email);
        if (!$user) {
            return false;
        }
        return password_verify($password, $user['password_hash']) ? $user : false;
    }
}
