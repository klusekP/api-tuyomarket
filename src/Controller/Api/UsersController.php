<?php

namespace App\Controller\Api;


use App\Document\User;
use App\Form\UserType;
use App\Form\UserWithClientType;
use App\Form\UserWithEmbeddedClientType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Swagger\Annotations as SWG;


class UsersController extends BaseApiController
{

    /**
     * @SWG\Response(response="200", description="List of requested objects")
     *
     * @SWG\Tag(name="users")
     *
     * @return Response
     */
    public function getUsersAction()
    {
        return $this->getObjectCollection(User::class);
    }

    /**
     * @SWG\Response(response="201", description="The requested resource was created successfully")
     * @SWG\Response(response="400", description="Errors occured while processing the request")
     * @SWG\Response(response="404", description="The requested object was not found")
     *
     * @SWG\Tag(name="users")
     *
     * @SWG\Parameter(
     *          name="requestBody",
     *          in="body",
     *          type="json",
     *          description="User data",
     *          required=true,
     *          @SWG\Schema(
     *              type="object",
     *              @SWG\Property(property="username", type="string", description="The user's username. Must be unique"),
     *              @SWG\Property(property="plainPassword", type="string", description="The user's password"),
     *              @SWG\Property(property="email", type="string", description="The user's email. Must be unique"),
     *              @SWG\Property(property="client", type="object", description="The clients ID or an object of clien's fields. See clients POST for reference",
     *              ),
     *          )
     *     ),
     *
     *
     * @param Request $request
     * @param $id
     * @return Response
     */
    public function postUsersAction(Request $request)
    {
        $user = new User();

        $preResponse = $this->handleUserUpdate($request, $user);

        if ($preResponse) {
            return $preResponse;
        }

        return $this->handleView($this->view(
            ['status' => 'ok'],
            Response::HTTP_CREATED,
            ["Location" => $this->generateUrl("get_user", ["id" => $user->getId()])]
        ));
    }

    /**
     *
     * @SWG\Response(response="200", description="Return a representation of the requested resource")
     * @SWG\Response(response="404", description="The requested object was not found")
     *
     * @SWG\Parameter(
     *     name="id",
     *     in="path",
     *     description="The ID of the requested object",
     *     type="string"
     * )
     * @SWG\Tag(name="users")
     *
     * @param string $id TThe ID of the requested object
     * @return Response
     */
    public function getUserAction($id)
    {
        return $this->getSingleObject(User::class, $id);
    }

    /**
     * @SWG\Response(response="200", description="Operation performed successfully")
     * @SWG\Response(response="400", description="Errors occured while processing the request")
     * @SWG\Response(response="404", description="The requested object was not found")
     *
     * @SWG\Parameter(
     *     name="id",
     *     in="path",
     *     description="The ID of the requested object",
     *     type="string"
     * )
     *
     * @SWG\Parameter(
     *          name="requestBody",
     *          in="body",
     *          type="json",
     *          description="User data",
     *          required=true,
     *          @SWG\Schema(
     *              type="object",
     *              @SWG\Property(property="username", type="string", description="The user's username. Must be unique"),
     *              @SWG\Property(property="plainPassword", type="string", description="The user's password"),
     *              @SWG\Property(property="email", type="string", description="The user's email. Must be unique"),
     *              @SWG\Property(property="client",  type="string", description="The client's ID if it should be changed"),
     *          )
     *     ),
     *
     * @SWG\Tag(name="users")
     *
     * @param Request $request
     * @param $id
     * @return Response
     */
    public function putUserAction(Request $request, $id)
    {
        $dm = $this->getDocumentManager();

        $shop = $dm->find(User::class, $id);

        if ($shop) {

            $preResponse = $this->handleUserUpdate($request, $shop);

            if ($preResponse) {
                return $preResponse;
            }

            return $this->handleView($this->view(
                ['status' => 'ok'],
                Response::HTTP_OK
            ));
        }

        return $this->return404();
    }

    /**
     * @SWG\Response(response="200", description="Operation performed successfully")
     * @SWG\Response(response="400", description="Errors occured while processing the request")
     * @SWG\Response(response="404", description="The requested object was not found")
     *
     * @SWG\Parameter(
     *     name="id",
     *     in="path",
     *     description="The ID of the requested object",
     *     type="string"
     * )
     *
     * @SWG\Tag(name="users")
     *
     * @param Request $request
     * @param $id
     * @return Response
     */
    public function deleteUserAction($id)
    {
        return $this->removeSingleObject(User::class, $id);
    }

    private function handleUserUpdate(Request $request, User $user)
    {
        $payload = $request->request->all();

        $user->addRole(User::ROLE_API);

        // check if the payload[client] is a valid array. If yes - assume that we try to create a user with a new client inside
        if (is_array($payload)) {
            if (array_key_exists('client', $payload)) {
                if (is_array($payload['client'])) {
                    $user->addRole(User::ROLE_CLIENT_ADMIN);
                    return $this->handleObjectUpdate($request, $user, UserWithEmbeddedClientType::class);
                } else {
                    return $this->handleObjectUpdate($request, $user, UserWithClientType::class);
                }
            } else {
                // if no client is present - we assume it's just an operation on the user
                return $this->handleObjectUpdate($request, $user, UserType::class);
            }
        }
        return $this->handleView($this->view(["status" => "wrong data format"], Response::HTTP_BAD_REQUEST));
    }

}