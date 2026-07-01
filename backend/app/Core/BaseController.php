<?php
namespace App\Core;

class BaseController
{
    protected function json(mixed $data, int $status = 200): void
    {
        http_response_code($status);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }

    protected function success(mixed $data = null, string $message = 'OK', int $status = 200): void
    {
        $this->json(['success' => true, 'message' => $message, 'data' => $data], $status);
    }

    protected function error(string $message, int $status = 400, array $errors = []): void
    {
        $this->json(['success' => false, 'message' => $message, 'errors' => $errors], $status);
    }

    protected function input(): array
    {
        $body = json_decode(file_get_contents('php://input'), true) ?? [];
        return array_merge($_POST, $body);
    }
}