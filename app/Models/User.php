<?php

class User {
    private $db;
    
    public function __construct($db) {
        $this->db = $db;
    }
    
    public function create($username, $email, $password) {
        // Get default home_room from .env
        $homeRoom = getenv('DEFAULT_HOME_ROOM') ?: 0;
        
        // Get user's IP
        $ip = $_SERVER['REMOTE_ADDR'];
        
        $data = [
            'username' => $username,
            'mail' => $email,
            'password' => password_hash($password, PASSWORD_DEFAULT),
            'look' => '-',
            'account_created' => time(),
            'ip_register' => $ip,
            'ip_current' => $ip,
            'home_room' => $homeRoom
        ];
        
        try {
            $query = "INSERT INTO users (username, mail, password, look, account_created, ip_register, ip_current, home_room) 
                     VALUES (:username, :mail, :password, :look, :account_created, :ip_register, :ip_current, :home_room)";
            
            $stmt = $this->db->prepare($query);
            $stmt->execute($data);
            
            return $this->db->lastInsertId();
        } catch (PDOException $e) {
            // Handle error appropriately
            throw new Exception("Error creating user: " . $e->getMessage());
        }
    }
}