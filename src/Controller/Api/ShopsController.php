<?php

namespace App\Controller\Api;

use App\Document\Shop;
use App\Form\ShopType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Swagger\Annotations as SWG;


class ShopsController extends BaseApiController
{

    /**
     * @SWG\Response(response="200", description="List of requested objects")
     *
     * @SWG\Tag(name="shops")
     *
     * @return Response
     */
    public function getShopsAction()
    {
        return $this->getObjectCollection(Shop::class);
    }

    /**
     * @SWG\Response(response="201", description="The requested resource was created successfully")
     * @SWG\Response(response="400", description="Errors occured while processing the request")
     * @SWG\Response(response="404", description="The requested object was not found")
     *
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
     *              @SWG\Property(property="email", type="string"),
     *              @SWG\Property(property="client", type="string", description="The client's ID"),
     *         )
     * )
     *
     * @SWG\Tag(name="shops")
     *
     * @param Request $request
     * @param $id
     * @return Response
     */
    public function postShopsAction(Request $request)
    {
        $shop = new Shop();

        $preResponse = $this->handleShopUpdate($request, $shop);

        if ($preResponse) {
            return $preResponse;
        }

        return $this->handleView($this->view(
            ['status' => 'ok'],
            Response::HTTP_CREATED,
            ["Location" => $this->generateUrl("get_shop", ["id" => $shop->getId()])]
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
     * @SWG\Tag(name="shops")
     *
     * @param string $id TThe ID of the requested object
     * @return Response
     */
    public function getShopAction($id)
    {
        return $this->getSingleObject(Shop::class, $id);
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
     *              @SWG\Property(property="email", type="string"),
     *              @SWG\Property(property="client",  type="string", description="The client's ID"),
     *         )
     * )
     *
     *
     * @SWG\Tag(name="shops")
     *
     * @param Request $request
     * @param $id
     * @return Response
     */
    public function putShopAction(Request $request, $id)
    {
        $dm = $this->getDocumentManager();

        $shop = $dm->find(Shop::class, $id);

        if ($shop) {

            $preResponse = $this->handleShopUpdate($request, $shop);

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
     *     description="The ID of the requested object",
     *     type="string"
     * )
     * @SWG\Tag(name="shops")
     *
     * @param string $id The ID of the requested object
     * @return Response
     */
    public function deleteShopAction($id)
    {
        return $this->removeSingleObject(Shop::class, $id);
    }

    private function handleShopUpdate($request, $shop)
    {
        return $this->handleObjectUpdate($request, $shop, ShopType::class);
    }

}