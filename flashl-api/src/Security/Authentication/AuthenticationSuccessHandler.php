<?php

namespace App\Security\Authentication;

use ApiPlatform\OpenApi\Model\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationSuccessHandlerInterface;

class AuthenticationSuccessHandler implements AuthenticationSuccessHandlerInterface
{
	public function onAuthenticationSuccess(Request $request, TokenInterface $token): JsonResponse
	{
		return new JsonResponse([
			'success' => true,
			'message' => 'Authentication successful',
		]);
	}
}
