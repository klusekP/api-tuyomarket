<?php

namespace App\Security\Authentication;


use FOS\UserBundle\Model\UserManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Encoder\JWTEncoderInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Exception\JWTDecodeFailureException;
use Lexik\Bundle\JWTAuthenticationBundle\TokenExtractor\AuthorizationHeaderTokenExtractor;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Guard\AbstractGuardAuthenticator;

class JwtTokenAuthenticator extends AbstractGuardAuthenticator
{

    private $jwtEncoder;
    private $userManager;

    public function __construct(JWTEncoderInterface $jwtEncoder, UserManagerInterface $userManager)
    {
        $this->jwtEncoder = $jwtEncoder;
        $this->userManager = $userManager;
    }

    public function start(Request $request, AuthenticationException $authException = null)
    {
        return new JsonResponse([
            'message' => 'Full authentication is required to access this resource.'
        ], JsonResponse::HTTP_UNAUTHORIZED);
    }

    /**
     * Does the authenticator support the given Request?
     *
     * If this returns false, the authenticator will be skipped.
     *
     * @param Request $request
     *
     * @return bool
     */
    public function supports(Request $request)
    {
        return true;
    }

    public function getCredentials(Request $request)
    {
        $extractor = new AuthorizationHeaderTokenExtractor(
            'Bearer',
            'Authorization'
        );

        $token = $extractor->extract($request);

        return $token;
    }

    public function getUser($credentials, UserProviderInterface $userProvider)
    {
        try {
            $data = $this->jwtEncoder->decode($credentials);

            $username = $data['username'];

            $user = $this->userManager->findUserBy(['email' => $username]);

            return $user;

        } catch (JWTDecodeFailureException $e) {
            throw new CustomUserMessageAuthenticationException('Invalid Token');
        }
    }

    public function checkCredentials($credentials, UserInterface $user)
    {
        return true;
    }


    public function onAuthenticationFailure(Request $request, AuthenticationException $exception)
    {
    }


    public function onAuthenticationSuccess(Request $request, TokenInterface $token, $providerKey)
    {
    }


    public function supportsRememberMe()
    {
        return false;
    }
}