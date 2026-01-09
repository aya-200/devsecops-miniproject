<?php

use PHPUnit\Framework\TestCase;

class RegisterTest extends TestCase
{
    private PDO $pdo;

    protected function setUp(): void
    {
        $host = 'localhost';
        $db   = 'notes_esigelec';
        $user = 'root';
        $pass = '';
        $charset = 'utf8mb4';

        $dsn = "mysql:host=$host;dbname=$db;charset=$charset";

        $this->pdo = new PDO($dsn, $user, $pass, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);
    }

    public function testPasswordHashing()
    {
        $password = 'Test123!';
        $hash = password_hash($password, PASSWORD_DEFAULT);

        $this->assertTrue(password_verify($password, $hash));
    }

    public function testUserInsertion()
    {
        $username = 'testuser';
        $email = 'testuser@example.com';
        $password = password_hash('Test123!', PASSWORD_DEFAULT);

        // Nettoyage avant test
        $this->pdo
            ->prepare("DELETE FROM users WHERE UserName = ?")
            ->execute([$username]);

        // Insertion
        $stmt = $this->pdo->prepare(
            "INSERT INTO users (UserName, email, Password, is_admin) VALUES (?, ?, ?, 0)"
        );
        $stmt->execute([$username, $email, $password]);

        // Vérification
        $stmt = $this->pdo->prepare(
            "SELECT * FROM users WHERE UserName = ?"
        );
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        $this->assertNotEmpty($user);
        $this->assertEquals($email, $user['email']);
    }
}
