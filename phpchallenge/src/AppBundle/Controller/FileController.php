<?php

namespace AppBundle\Controller;

use AppBundle\Entity\File;
use AppBundle\Collections\FileCollection;
use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\View\View;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;

class FileController extends FOSRestController
{

    /**
     * @Route("/files", name = "file_list")
     * @Method({"GET"})
     * @ApiDoc(
     *     resource = true,
     *     description = "Returns a collection of Files",
     *     filters = {
     *         {
     *             "name" = "filename",
     *             "dataType" = "string",
     *             "description" = "Filter files by filename"
     *         },
     *         {
     *             "name" = "limit",
     *             "dataType" = "integer",
     *             "description" = "How many resources should be returned"
     *         },
     *         {
     *             "name" = "offset",
     *             "dataType" = "integer",
     *             "description" = "How many resources should be skipped"
     *         },
     *     },
     *     output = {
     *         "collection" = true,
     *         "collectionName" = "files",
     *         "class" = "AppBundle\Entity\File"
     *     },
     *     statusCodes = {
     *         200 = "Returned when successful",
     *         403 = "Returned when the action is unauthorized",
     *         401 = "Returned when not authenticated",
     *         400 = "Returned when the request is invalid",
     *         404 = "Returned when no resources are found",
     *         500 = "Returned when an error occurs",
     *     }
     * )
     */
    public function listAction(Request $request)
    {
        $view = new View();

        $repo = $this->getDoctrine()
            ->getManager()
            ->getRepository("AppBundle:File");

        $query = $repo->createQueryBuilder("f")
        ->select("f");

        if ($request->query->has("filename")) {
            $query->where(
                $query->expr()->like("f.filename", ":filename")
            )->setParameter(":filename", "%" . $request->query->get("filename") . "%");
        }

        $files = $query->getQuery()->getResult();

        $view->setData(new FileCollection($files));

        return $this->handleView($view);
    }


    /**
     * @Route("/files/{id}", name = "file_show", requirements = {"id" = "\d+"})
     * @Method({"GET"})
     * @ParamConverter("files", class="AppBundle:File")
     * @ApiDoc(
     *     description = "Returns file metadata based on its ID",
     *     output = {
     *         "class" = "AppBundle\Entity\File"
     *     },
     *     statusCodes = {
     *         200 = "Returned when successful",
     *         403 = "Returned when the action is unauthorized",
     *         401 = "Returned when not authenticated",
     *         400 = "Returned when the request is invalid",
     *         404 = "Returned when no resources are found",
     *         500 = "Returned when an error occurs",
     *     }
     * )
     * */
    public function showFileAction(Request $request, File $file)
    {
        $view = new View();
        $view->setData($file);
        return $this->handleView($view);
    }



    /**
     * @Route("/files/{id}/content", name="file_content", requirements={"id" = "\d+"})
     * @Method({"GET"})
     * @ParamConverter("file", class="AppBundle:File")
     * @ApiDoc(
     *     description = "Returns the file content based on its ID",
     *     statusCodes = {
     *         200 = "Returned when successful",
     *         403 = "Returned when the action is unauthorized",
     *         401 = "Returned when not authenticated",
     *         400 = "Returned when the request is invalid",
     *         404 = "Returned when no resources are found",
     *         500 = "Returned when an error occurs",
     *     }
     * )
     * */
    public function getFileContentAction(Request $request, File $file)
    {

    }


    /**
     * @Route("/files", name="file_store")
     * @Method({"POST"})
     * @ApiDoc(
     *     description = "Inserts new file metadata in the database",
     *     input = {
     *         "class" = "AppBundle\Form\FileType"
     *     },
     *     output = {
     *         "class" = "AppBundle\Entity\File"
     *     },
     *     statusCodes = {
     *         201 = "Returned when the operation is successful and a new resource is created",
     *         403 = "Returned when the action is unauthorized",
     *         401 = "Returned when not authenticated",
     *         400 = "Returned when the request is invalid",
     *         500 = "Returned when an error occurs",
     *     }
     * )
     * */
    public function storeFileAction(Request $request)
    {
        $view = new View();
        $file = new File();

        $manager = $this->getDoctrine()->getManager();

        $file->setFilename($request->get("filename"))
        ->setStatus((integer) $request->get("status"))
        ->setCreated_at(date_create_from_format("Y-m-d H:i:s", date("Y-m-d H:i:s")));

        $validator = $this->get("validator");
        $errors = $validator->validate($file);

        if (count($errors)) {
            $view->setStatusCode(400);
            $view->setData($errors);
        } else {
            try {
                $manager->persist($file);
                $manager->flush();

                $view->setData($file);
                $view->setStatusCode(201);
            } catch (\Doctrine\DBAL\DBALException $e) {
                $view->setStatusCode(500);
                $view->setData($e->getMessage());
            }
        }

        return $this->handleView($view);
    }


    /**
     * @Route("/files/{id}/content", name = "file_content_store", requirements = {"id" = "\d+"})
     * @Method({"POST"})
     * @ParamConverter("file", class="AppBundle:File")
     * @ApiDoc(
     *     description = "Uploads the file contents to the storage based on its ID",
     *     statusCodes = {
     *         201 = "Returned when the operation is successful and a new resource is created",
     *         403 = "Returned when the action is unauthorized",
     *         401 = "Returned when not authenticated",
     *         400 = "Returned when the request is invalid",
     *         500 = "Returned when an error occurs",
     *     }
     * )
     * */
    public function storeFileContentAction(Request $request, File $file)
    {

    }


    /**
     * @Route("/files/{id}", name="file_delete", requirements = {"id" = "\d+"})
     * @Method({"DELETE"})
     * @ParamConverter("file", class="AppBundle:File")
     * @ApiDoc(
     *     description = "Deletes a file from the database and its contents from the storage",
     *     statusCodes = {
     *         204 = "Returned when successful.",
     *         403 = "Returned when the action is unauthorized",
     *         401 = "Returned when not authenticated",
     *         400 = "Returned when the request is invalid",
     *         404 = "Returned when no resources are found",
     *         500 = "Returned when an error occurs",
     *     }
     * )
     * */
    public function deleteFileAction(Request $request, File $file)
    {
        $view = new View();
        try {
            $manager = $this->getDoctrine()->getManager();
            $manager->remove($file);
            $manager->flush();

            $view->setStatusCode(204);
        } catch (Exception $e) {
            $view->setData(array("message"=>$e->getMessage()));
            $view->setStatusCode(500);
        }

        return $this->handleView($view);
    }


    /**
     * @Route("/files/{id}", name="file_replace_metadata", requirements = {"id" = "\d+"})
     * @Method({"PUT"})
     * @ParamConverter("file", class="AppBundle:File")
     * @ApiDoc(
     *     description = "Completely replaces/updates file metadata based on ID",
     *     input = {
     *         "class" = "AppBundle\Form\FileType"
     *     },
     *     statusCodes = {
     *         204 = "Returned when successful",
     *         403 = "Returned when the action is unauthorized",
     *         401 = "Returned when not authenticated",
     *         400 = "Returned when the request is invalid",
     *         500 = "Returned when an error occurs",
     *     }
     * )
     * */
    public function replaceFileMetadataAction(Request $request, File $file)
    {

    }



    /**
     * @Route("/files/{id}", name="file_update_metadata", requirements = {"id" = "\d+"})
     * @Method({"PATCH"})
     * @ParamConverter("file", class="AppBundle:File")
     * @ApiDoc(
     *     description = "Partialy updates file metadata based on ID",
     *     input = {
     *         "class" = "AppBundle\Form\FileType"
     *     },
     *     statusCodes = {
     *         204 = "Returned when successful",
     *         403 = "Returned when the action is unauthorized",
     *         401 = "Returned when not authenticated",
     *         400 = "Returned when the request is invalid",
     *         500 = "Returned when an error occurs",
     *     }
     * )
     * */
    public function patchFileMetadataAction(Request $request, File $file)
    {

    }



    /**
     * @Route("/files/{id}/content", name="file_replace_content", requirements = {"id" = "\d+"})
     * @Method({"PUT"})
     * @ParamConverter("file", class="AppBundle:File")
     * @ApiDoc(
     *     description = "Replace file content based on ID",
     *     statusCodes = {
     *         204 = "Returned when successful",
     *         403 = "Returned when the action is unauthorized",
     *         401 = "Returned when not authenticated",
     *         400 = "Returned when the request is invalid",
     *         500 = "Returned when an error occurs",
     *     }
     * )
     * */
    public function updateFileContentAction(Request $request, File $file)
    {

    }

}
