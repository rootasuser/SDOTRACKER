<?php 

namespace App\Modules\Auth;

require '../../Config/Config.php';

use App\Config\Config;
use PDO;
use PDOException;

header('Content-Type: application/json');

session_start(); 

class Login extends Config
{
    public function __construct()
    {
        parent::__construct();
    }

    public function authenticate(): void
    {
       
        $username = $this->sanitizeInput($_POST['username'] ?? '');
        $password = $this->sanitizeInput($_POST['password'] ?? '');

        if (empty($username) || empty($password)) {
            echo json_encode([
                'success' => false,
                'message' => 'Username and password are required.'
            ]);
            exit;
        }

        try {
          
            $user = $this->getUserByUsername($username);

       
            if ($user && $this->isPasswordValid($password, $user['password'])) {
                session_regenerate_id(true);

                $_SESSION['user'] = $user;

                $this->logAction($username, "$username successfully logged in");

                echo json_encode([
                    'success' => true,
                    'message' => 'Login successful.'
                ]);
                exit;
            }

            echo json_encode([
                'success' => false,
                'message' => 'Invalid username or password.'
            ]);
            exit;
        } catch (PDOException $e) {

            error_log('Database error: ' . $e->getMessage()); 
            echo json_encode([
                'success' => false,
                'message' => 'An error occurred. Please try again later.'
            ]);
            exit;
        } catch (\Exception $e) {
        
            error_log('General error: ' . $e->getMessage());
            echo json_encode([
                'success' => false,
                'message' => 'An unexpected error occurred.'
            ]);
            exit;
        }
    }

    private function sanitizeInput(string $input): string
    {
        return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
    }

    private function getUserByUsername(string $username): ?array
    {
        $query = "SELECT * FROM users WHERE username = :username";
        $stmt = $this->DB_CONNECTION->prepare($query);
        $stmt->bindParam(':username', $username, PDO::PARAM_STR);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    private function isPasswordValid(string $password, string $hashedPassword): bool
    {
        return password_verify($password, $hashedPassword);
    }

    private function logAction(string $username, string $action): void
    {
        $query = "INSERT INTO logs (username, action, created_at) VALUES (:username, :action, NOW())";
        $stmt = $this->DB_CONNECTION->prepare($query);
        $stmt->bindParam(':username', $username, PDO::PARAM_STR);
        $stmt->bindParam(':action', $action, PDO::PARAM_STR);
        $stmt->execute();
    }
}


$login = new Login();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_GET['action'] === 'login') {
   
    $login->authenticate();
} else {

    echo json_encode([
        'success' => false,
        'message' => 'Invalid request method or action.'
    ]);
    exit;
}
