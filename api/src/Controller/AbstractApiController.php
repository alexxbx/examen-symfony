<?php
// src/Controller/AbstractApiController.php

namespace App\Controller;

use App\Service\ErrorNormalizer;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\ConstraintViolationListInterface;

abstract class AbstractApiController extends AbstractController
{
    protected ErrorNormalizer $errorNormalizer;
    
    public function __construct(ErrorNormalizer $errorNormalizer)
    {
        $this->errorNormalizer = $errorNormalizer;
    }
    
    /**
     * Décode le contenu JSON de la requête
     */
    protected function decodeJsonRequest(Request $request): array
    {
        try {
            return json_decode($request->getContent(), true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException $e) {
            throw new \InvalidArgumentException('Invalid JSON format');
        }
    }
    
    /**
     * Normalise et retourne les erreurs de validation
     */
    protected function createValidationErrorResponse(ConstraintViolationListInterface $errors): JsonResponse
    {
        return $this->json(
            $this->errorNormalizer->normalize($errors),
            Response::HTTP_BAD_REQUEST
        );
    }
    
    /**
     * Crée une réponse pour une ressource introuvable
     */
    protected function createNotFoundResponse(string $resourceName): JsonResponse
    {
        return $this->json(
            ['error' => "$resourceName introuvable"],
            Response::HTTP_NOT_FOUND
        );
    }
}