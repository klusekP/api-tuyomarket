<?php

namespace App\Controller\Api;

use App\Document\Client;
use App\Form\ClientType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Swagger\Annotations as SWG;

class ClientsController extends BaseApiController
{

    /**
     * @SWG\Response(response="200", description="List of requested objects")
     *
     * @SWG\Tag(name="clients")
     *
     * @return Response
     */
    public function getClientsAction()
    {
        return $this->getObjectCollection(Client::class);
    }

    /**
     * @SWG\Response(response="201", description="The requested resource was created successfully")
     * @SWG\Response(response="400", description="Errors occured while processing the request")
     * @SWG\Response(response="404", description="The requested object was not found")
     *
     *
     *  @SWG\Parameter(
     *          name="requestBody",
     *          in="body",
     *          type="json",
     *          description="Client data",
     *          required=true,
     *          @SWG\Schema(
     *              @SWG\Property(property="name", type="string"),
     *              @SWG\Property(property="phone", type="string"),
     *              @SWG\Property(property="address", type="string"),
     *              @SWG\Property(property="nip", type="string", uniqueItems=true),
     *              @SWG\Property(property="regon", type="string"),
     *              @SWG\Property(property="email", type="string")
     *         )
     * )
     *
     * @SWG\Tag(name="clients")
     *
     * @param Request $request
     * @param $id
     * @return Response
     */
    public function postClientsAction(Request $request)
    {
        $client = new Client();

        $preResponse = $this->handleClientUpdate($request, $client);

        if ($preResponse) {
            return $preResponse;
        }

        return $this->handleView($this->view(
            ['status' => 'ok', 'id' => $client->getId()],
            Response::HTTP_CREATED,
            ["Location" => $this->generateUrl("get_client", ["id" => $client->getId()])]
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
     *     description="The unique identifier or NIP number of a client",
     *     type="string"
     * )
     * @SWG\Tag(name="clients")
     *
     * @param string $id The unique identifier or NIP number of a client
     * @return Response
     */
    public function getClientAction($id)
    {
        return $this->renderSingleObject($this->getClientByIdentifier($id));
    }

    /**
     * @SWG\Response(response="200", description="Operation performed successfully")
     * @SWG\Response(response="400", description="Errors occured while processing the request")
     * @SWG\Response(response="404", description="The requested object was not found")
     *
     * @SWG\Parameter(
     *     name="id",
     *     in="path",
     *     description="The unique identifier or NIP number of a client",
     *     type="string"
     * )
     *  @SWG\Parameter(
     *          name="requestBody",
     *          in="body",
     *          type="json",
     *          description="User data",
     *          required=true,
     *          @SWG\Schema(
     *              @SWG\Property(property="name", type="string"),
     *              @SWG\Property(property="phone", type="string"),
     *              @SWG\Property(property="address", type="string"),
     *              @SWG\Property(property="nip", type="string", uniqueItems=true),
     *              @SWG\Property(property="regon", type="string"),
     *              @SWG\Property(property="email", type="string")
     *         )
     * )
     *
     * @SWG\Tag(name="clients")
     *
     * @param Request $request
     * @param $id
     * @return Response
     * @Security("is_granted('ROLE_USER_EDIT')")
     */
    public function putClientAction(Request $request, $id)
    {

        if ($client = $this->getClientByIdentifier($id)) {

            $preResponse = $this->handleClientUpdate($request, $client);

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
     *
     * @SWG\Response(response="204", description="The requested object has been deleted")
     * @SWG\Response(response="404", description="The requested object was not found")
     *
     * @SWG\Parameter(
     *     name="id",
     *     in="path",
     *     description="The unique identifier or NIP number of a client",
     *     type="string"
     * )
     * @SWG\Tag(name="clients")
     *
     * @param string $id The unique identifier or NIP number of a client
     * @return Response
     */
    public function deleteClientAction($id)
    {
        return $this->deleteSingleObject($this->getClientByIdentifier($id));
    }

    private function handleClientUpdate($request, $client)
    {
        return $this->handleObjectUpdate($request, $client, ClientType::class);
    }


    private function getClientByIdentifier($identifier)
    {
        $dm = $this->getDocumentManager();

        $client = $dm->find(Client::class, $identifier);

        if (!$client) {
            $client = $dm->getRepository(Client::class)->findOneBy(["nip" => $identifier]);
        }

        return $client;
    }
}