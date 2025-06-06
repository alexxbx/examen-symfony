<?php
// src/DTO/ApiResponse.php

namespace App\DTO;

class ApiResponse
{
    private bool $success;
    private ?string $message;
    private ?array $data;
    private ?array $errors;
    
    private function __construct(bool $success, ?string $message = null, ?array $data = null, ?array $errors = null)
    {
        $this->success = $success;
        $this->message = $message;
        $this->data = $data;
        $this->errors = $errors;
    }
    
    public static function success(?array $data = null, ?string $message = null): self
    {
        return new self(true, $message, $data, null);
    }
    
    public static function error(?array $errors = null, ?string $message = null): self
    {
        return new self(false, $message, null, $errors);
    }
    
    public function toArray(): array
    {
        $response = [
            'success' => $this->success
        ];
        
        if ($this->message !== null) {
            $response['message'] = $this->message;
        }
        
        if ($this->data !== null) {
            $response['data'] = $this->data;
        }
        
        if ($this->errors !== null) {
            $response['errors'] = $this->errors;
        }
        
        return $response;
    }
}