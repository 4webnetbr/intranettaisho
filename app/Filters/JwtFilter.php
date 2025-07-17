<?php

namespace App\Filters;

use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Filters\FilterInterface;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class JwtFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $authHeader = $request->getHeaderLine('Authorization');

        if (!$authHeader) {
            return service('response')->setJSON(['message' => 'Token não enviado'])->setStatusCode(401);
        }

        $token = trim($authHeader);

        try {
            // Substitua pela sua chave secreta real
            $secretKey = getenv('JWT_SECRET_KEY') ?? 'sua_chave_secreta';
            $decoded = JWT::decode($token, new Key($secretKey, 'HS256'));

            // Armazena o payload no serviço de requisição para uso posterior
            $request->userPayload = $decoded;

        } catch (\Throwable $e) {
            return service('response')->setJSON([
                'message' => 'Token inválido ou expirado',
                'erro' => $e->getMessage()
            ])->setStatusCode(401);
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // Nada a fazer após
    }
}
