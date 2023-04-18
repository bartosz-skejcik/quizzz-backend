<?php
require_once __DIR__ . "/../model/Database.php";

class UserModel extends Database
{
    public function get($userId)
    {
        return $this->select("SELECT * FROM Users WHERE id = ?", ["i", $userId]);
    }

    public function getByUsername($username)
    {
        return $this->select("SELECT * FROM Users WHERE username = ?", ["s", $username]);
    }

    public function getAll()
    {
        return $this->select("SELECT * FROM Users");
    }

    public function create($username, $password, $email, $fullName)
    {
        $this->execute("INSERT INTO Users (username, password, email, full_name) VALUES (?, '$password', '$email', '$fullName')", ["s", $username]);
    }

    public function verify($username, $password)
    {
        $result = $this->select("SELECT * FROM Users WHERE username = ? AND password = '$password'", ["s", $username]);
        if (count($result) > 0) {
            return $result[0];
        }
        return null;
    }
}
