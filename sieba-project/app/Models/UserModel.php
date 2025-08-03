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
    protected $protectFields = true;
    
    protected $allowedFields = [
        'nama',
        'email',
        'password',
        'no_hp',
        'institusi',
        'jabatan',
        'alamat',
        'tanggal_lahir',
        'jenis_kelamin',
        'role',
        'is_verified',
        'verification_token',
        'avatar'
    ];

    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    protected $validationRules = [
        'nama' => 'required|min_length[3]|max_length[100]',
        'email' => 'required|valid_email|is_unique[users.email,id,{id}]',
        'password' => 'required|min_length[8]',
        'no_hp' => 'required|numeric|min_length[10]|max_length[15]',
        'role' => 'in_list[user,admin]',
        'jenis_kelamin' => 'in_list[L,P]'
    ];

    protected $validationMessages = [
        'nama' => [
            'required' => 'Nama lengkap harus diisi',
            'min_length' => 'Nama minimal 3 karakter',
            'max_length' => 'Nama maksimal 100 karakter'
        ],
        'email' => [
            'required' => 'Email harus diisi',
            'valid_email' => 'Format email tidak valid',
            'is_unique' => 'Email sudah terdaftar'
        ],
        'password' => [
            'required' => 'Password harus diisi',
            'min_length' => 'Password minimal 8 karakter'
        ],
        'no_hp' => [
            'required' => 'Nomor HP harus diisi',
            'numeric' => 'Nomor HP harus berupa angka',
            'min_length' => 'Nomor HP minimal 10 digit',
            'max_length' => 'Nomor HP maksimal 15 digit'
        ]
    ];

    protected $skipValidation = false;
    protected $beforeInsert = ['hashPassword'];
    protected $beforeUpdate = ['hashPassword'];

    /**
     * Hash password before saving
     */
    protected function hashPassword(array $data)
    {
        if (isset($data['data']['password'])) {
            $data['data']['password'] = password_hash($data['data']['password'], PASSWORD_DEFAULT);
        }

        return $data;
    }

    /**
     * Verify user login
     */
    public function verifyLogin($email, $password)
    {
        $user = $this->where('email', $email)->first();

        if ($user && password_verify($password, $user['password'])) {
            // Remove password from returned data for security
            unset($user['password']);
            return $user;
        }

        return false;
    }

    /**
     * Create new user account
     */
    public function createUser($data)
    {
        // Generate verification token
        $data['verification_token'] = bin2hex(random_bytes(32));
        $data['is_verified'] = false;
        $data['role'] = 'user'; // Default role

        return $this->insert($data);
    }

    /**
     * Verify email with token
     */
    public function verifyEmail($token)
    {
        $user = $this->where('verification_token', $token)->first();

        if ($user) {
            return $this->update($user['id'], [
                'is_verified' => true,
                'verification_token' => null
            ]);
        }

        return false;
    }

    /**
     * Update user profile
     */
    public function updateProfile($userId, $data)
    {
        // Remove sensitive fields that shouldn't be updated via profile
        unset($data['password'], $data['role'], $data['is_verified']);

        return $this->update($userId, $data);
    }

    /**
     * Change password
     */
    public function changePassword($userId, $oldPassword, $newPassword)
    {
        $user = $this->find($userId);

        if ($user && password_verify($oldPassword, $user['password'])) {
            return $this->update($userId, ['password' => $newPassword]);
        }

        return false;
    }

    /**
     * Get user statistics for admin dashboard
     */
    public function getStatistik()
    {
        $total = $this->countAll();
        $verified = $this->where('is_verified', true)->countAllResults();
        $thisMonth = $this->where('created_at >=', date('Y-m-01 00:00:00'))
                         ->countAllResults();

        return [
            'total' => $total,
            'verified' => $verified,
            'unverified' => $total - $verified,
            'this_month' => $thisMonth
        ];
    }

    /**
     * Search users (for admin)
     */
    public function searchUsers($search = null, $role = null, $perPage = 20)
    {
        $builder = $this->select('id, nama, email, no_hp, institusi, role, is_verified, created_at');

        if ($search) {
            $builder->groupStart()
                   ->like('nama', $search)
                   ->orLike('email', $search)
                   ->orLike('institusi', $search)
                   ->groupEnd();
        }

        if ($role) {
            $builder->where('role', $role);
        }

        return $builder->orderBy('created_at', 'DESC')
                      ->paginate($perPage);
    }

    /**
     * Get user profile with statistics
     */
    public function getUserProfile($userId)
    {
        $user = $this->select('id, nama, email, no_hp, institusi, jabatan, alamat, tanggal_lahir, jenis_kelamin, avatar, created_at')
                    ->find($userId);

        if ($user) {
            // Get user's event participation count
            $pendaftaranModel = new PendaftaranModel();
            $user['total_events'] = $pendaftaranModel->where('user_id', $userId)
                                                   ->countAllResults();
            
            $user['completed_events'] = $pendaftaranModel->where('user_id', $userId)
                                                       ->where('status', 'completed')
                                                       ->countAllResults();
        }

        return $user;
    }

    /**
     * Reset password request
     */
    public function requestPasswordReset($email)
    {
        $user = $this->where('email', $email)->first();

        if ($user) {
            $token = bin2hex(random_bytes(32));
            $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));

            return $this->update($user['id'], [
                'reset_token' => $token,
                'reset_expires' => $expires
            ]);
        }

        return false;
    }

    /**
     * Reset password with token
     */
    public function resetPassword($token, $newPassword)
    {
        $user = $this->where('reset_token', $token)
                    ->where('reset_expires >', date('Y-m-d H:i:s'))
                    ->first();

        if ($user) {
            return $this->update($user['id'], [
                'password' => $newPassword,
                'reset_token' => null,
                'reset_expires' => null
            ]);
        }

        return false;
    }
}