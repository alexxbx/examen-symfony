<?php
// src/Service/ErrorNormalizer.php

namespace App\Service;

use Symfony\Component\Validator\ConstraintViolationListInterface;

class ErrorNormalizer
{
    public function normalize(ConstraintViolationListInterface $errors): array
    {
        $normalized = [];
        foreach ($errors as $error) {
            $normalized[] = [
                'property' => $error->getPropertyPath(),
                'message' => $error->getMessage(),
            ];
        }
        return $normalized;
    }
}