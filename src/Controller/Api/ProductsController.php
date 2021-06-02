<?php

namespace App\Controller\Api;

use App\Document\Products;
use FOS\RestBundle\Request\ParamFetcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Swagger\Annotations as SWG;

class ProductsController extends BaseApiController
{
    /**
     * @SWG\Response(response="200", description="List of requested objects")
     *
     * @SWG\Tag(name="products")
     *
     * @param ParamFetcherInterface $paramFetcher
     * @return Response
     */
    public function cgetAction(ParamFetcherInterface $paramFetcher)
    {
        return $this->getObjectCollection(Products::class);
    }

    /**
     * @param Request $request
     */
    public function postAction(Request $request)
    {

    }
}
