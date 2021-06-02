<?php

namespace App\Controller\Api;


use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;
use Swagger\Annotations as SWG;


class TokensController extends BaseApiController
{
    /**
     *
     * @SWG\Response(response="200", description="The authentication was successfull")
     * @SWG\Response(response="400", description="The request was malformed")
     * @SWG\Response(response="401", description="The authentication failed")
     * @SWG\Response(response="404", description="The requested user was not found")
     *
     * @SWG\Parameter(
     *          name="requestBody",
     *          in="body",
     *          type="json",
     *          description="User authentication data",
     *          required=true,
     *          @SWG\Schema(
     *              type="object",
     *              @SWG\Property(property="auth", type="object",
     *                  @SWG\Property(property="username", type="string", description="The email used as a username"),
     *                  @SWG\Property(property="password", type="string", description="The user's password")
     *              ),
     *          )
     *     ),
     *
     * @SWG\Tag(name="tokens")
     *
     * @Route("/api/tokens", methods={"POST"})
     *
     * @param Request $request
     * @return Response
     * @throws \Lexik\Bundle\JWTAuthenticationBundle\Exception\JWTEncodeFailureException
     */
    public function postTokenAction(Request $request)
    {
        $json = $request->getContent();

        $data = json_decode($json, true);

        if (array_key_exists("auth", $data)) {

            $authData = $data["auth"];

            $user = $this->get('fos_user.user_manager')->findUserBy(['email' => $authData["username"]]);

            if ($user) {

                $isValid = $this->get('security.password_encoder')
                    ->isPasswordValid($user, $authData['password']);

                if ($isValid) {
                    $token = $this->get('lexik_jwt_authentication.encoder')->encode([
                        "username" => $user->getEmailCanonical(),
                        'exp' => time() + 3600
                    ]);

                    return $this->handleView($this->view(["access_token" => $token]));

                } else {
                    throw new BadCredentialsException();
                }
            } else {
                throw new BadCredentialsException();
            }
        }
        return $this->handleView($this->view(["status" => "wrong data format"], Response::HTTP_BAD_REQUEST));
    }

}
