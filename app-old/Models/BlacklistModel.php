<?php

namespace App\Models;

use CodeIgniter\Model;

class BlacklistModel extends Model
{
    protected $table = 'blacklisted_tokens';
    protected $primaryKey = 'id';
    protected $allowedFields = ['token', 'created_at', 'expires_at'];
    protected $useTimestamps = true;

    public function addToken($token)
    {
       
        // Check if the token already exists in the blacklist
        $existingToken = $this->where('token', $token)->first();
    
        if ($existingToken) {
            // Token already exists, return false or handle as needed
            return false; // Indicating the token was not added
        }
    
        // Prepare the data for insertion
        $data = [
            'token' => $token,
            'created_at' => date('Y-m-d H:i:s'),
            'expires_at' => date('Y-m-d H:i:s', time() + 3600) // Example: expires in 1 hour
        ];
    
        // Insert the new token
        return $this->insert($data);
    }
    
	
	public function isBlacklisted($token)
    {
        return $this->where('token', $token)->first() !== null;
    }
}
