<?php
require __DIR__ . '/vendor/autoload.php';

use Symfony\Component\Security\Core\User\InMemoryUser;

$kernel = new App\Kernel('dev', true);
$kernel->boot();

$jwtManager = $kernel->getContainer()->get('lexik_jwt_authentication.jwt_manager');

// Créer un utilisateur fictif pour le test
$user = new InMemoryUser('testuser', null, ['ROLE_USER']);

// Générer un JWT
echo $jwtManager->create($user) . PHP_EOL;
