<?php


namespace App\Controller\Api;

use FOS\RestBundle\Controller\FOSRestController;
use MongoDuplicateKeyException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;


class BaseApiController extends FOSRestController
{
    protected function getDocumentManager()
    {
        $dm = $this->get('doctrine_mongodb')->getManager();

        $dm->getSchemaManager()->ensureIndexes();

        return $dm;
    }

    protected function getObjectCollection($className)
    {

        $dm = $this->getDocumentManager();

        $data = $dm->getRepository($className)->findAll();

        if ($data) {
            $view = $this->view($data, Response::HTTP_OK);
        } else {
            $view = $this->view($data, Response::HTTP_NOT_FOUND);
        }

        return $this->handleView($view);
    }


    protected function getSingleObject($className, $id)
    {
        $dm = $this->getDocumentManager();

        $object = $dm->find($className, $id);

        return $this->renderSingleObject($object);
    }

    protected function renderSingleObject($object = null)
    {

        if ($object) {

            $view = $this->view($object, Response::HTTP_OK);

            return $this->handleView($view);
        }
        return $this->return404();
    }

    protected function removeSingleObject($className, $id)
    {
        $dm = $this->getDocumentManager();

        $object = $dm->find($className, $id);

        return $this->deleteSingleObject($object);
    }

    protected function deleteSingleObject($object = null)
    {

        if ($object) {

            $dm = $this->getDocumentManager();

            $dm->remove($object);

            $dm->flush();

            return $this->handleView($this->view([], Response::HTTP_NO_CONTENT));
        }

        return $this->return404();
    }

    protected function return404()
    {
        return $this->handleView($this->view(["status" => "not found"], Response::HTTP_NOT_FOUND));
    }

    protected function handleObjectUpdate(Request $request, $object, $formClass, $formOptions = [])
    {
        $form = $this->createForm($formClass, $object, $formOptions);

        $this->preSubmit($object);

        $form->submit($request->request->all());

        $this->postSubmit($object);

        if (false === $form->isValid()) {

            return $this->handleView(
                $this->view($form)
            );
        }

        try {
            $dm = $this->getDocumentManager();

            $dm->persist($object);

            $dm->flush();

        } catch (MongoDuplicateKeyException $e) {

            // collection has an UniqueIndex on some fields, if the Index is violated, return 400

            return $this->handleView($this->view(
                ['message' => $e->getMessage()],
                Response::HTTP_BAD_REQUEST
            ));
        }
    }

    protected function preSubmit(&$object)
    {
    }

    protected function postSubmit(&$object)
    {
    }

}