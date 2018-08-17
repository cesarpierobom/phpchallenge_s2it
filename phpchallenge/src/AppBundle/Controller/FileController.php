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
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpFoundation\File\MimeType\FileinfoMimeTypeGuesser;


class FileController extends FOSRestController
{

    /**
     * @Route("/api/v1/files", name = "file_list")
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
     *             "name" = "status",
     *             "dataType" = "integer",
     *             "description" = "Filter files by status. 1 = active, 0 = inactive"
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

        $files = $repo->findAllCustom(
        	$request->query->get("filename"),
        	(array) $request->query->get("status"),
        	$request->query->get("limit"),
        	$request->query->get("offset")
        );

        $view->setData(new FileCollection($files));

        return $this->handleView($view);
    }


    /**
     * @Route("/api/v1/files/{id}", name = "file_show", requirements = {"id" = "\d+"})
     * @Method({"GET"})
     * @ParamConverter("files", class="AppBundle:File", options = {"repository_method" = "findNotDeleted"})
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

     * @Route("/api/v1/files/{id}/content", name="get_file_content", requirements={"id" = "\d+"})
     * @Method({"GET"})
     * @ParamConverter("file", class="AppBundle:File", options = {"repository_method" = "findNotDeleted"})
     * @ApiDoc(
     *     description = "Returns the file content based on its ID. (It may crash documentation sandbox)",
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
    	$view = new View();

    	$path = $this->get("kernel")->getRootDir() . DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR . "uploads";
    	$filename = $path . DIRECTORY_SEPARATOR . $file->getInternalFilename();
    	$originalFilename = $file->getFilename();

    	try {
    		if ("" != $file->getInternalFilename() && file_exists($filename)) {
    			$response = new BinaryFileResponse($filename);
                $response->trustXSendfileTypeHeader();
    			$mimeTypeGuesser = new FileinfoMimeTypeGuesser();
    			$response->headers->set("Content-Type", $mimeTypeGuesser->guess($filename));
    			$response->setContentDisposition(ResponseHeaderBag::DISPOSITION_ATTACHMENT, $file->getFilename());
    			return $response;
    		} else {
    			$view->setStatusCode(404);
    			$view->setData(array("message"=>"NOT FOUND"));
        		return $this->handleView($view);
    		}
    	} catch (Exception $e) {
        	$view->setStatusCode(500);
        	$view->setData($e);
        	return $this->handleView($view);
    	}
    }


    /**
     * @Route("/api/v1/files", name="file_store")
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

        $repo = $this->getDoctrine()->getManager()->getRepository("AppBundle:File");

        $file->setFilename($request->get("filename"))
        ->setStatus((integer) $request->get("status"))
        ->setCreatedAt(date_create_from_format("Y-m-d H:i:s", date("Y-m-d H:i:s")));

        $validator = $this->get("validator");
        $errors = $validator->validate($file);

        if (count($errors)) {
            $view->setStatusCode(400);
            $view->setData($errors);
        } else {
            try {
                $repo->insert($file);

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
     * @Route("/api/v1/files/{id}/content", name = "file_content_store", requirements = {"id" = "\d+"})
     * @Method({"POST"})
     * @ParamConverter("file", class="AppBundle:File", options = {"repository_method" = "findNotDeleted"})
     * @ApiDoc(
     *     description = "Uploads the file contents to the storage based on its ID",
     *     parameters = {
     *         {"name" = "file", "dataType" = "file", "required" = true, "description" = "File to be uploaded"}
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
    public function storeFileContentAction(Request $request, File $file)
    {
    	$view = new View();
    	$path = $this->get("kernel")->getRootDir() . DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR . "uploads";
    	$uploaded = $request->files->get("file");
    	$newFilename = $this->generateUniqueInternalFilename();

    	if ("" != $file->getInternalFilename() && file_exists($path . DIRECTORY_SEPARATOR . $file->getInternalFilename())) {
    		$view->setStatusCode(409);
    		$view->setData(array("message" => "File already exists. Use PUT method to replace it."));
            return $this->handleView($view);
    	}

    	try {
    		$file->setFilename($uploaded->getClientOriginalName());
    		$file->setInternalFilename($newFilename);
    		$repo = $this->getDoctrine()->getManager()->getRepository("AppBundle:File");
			$repo->update($file);

    		$uploaded->move($path , $newFilename);
    		$view->setStatusCode(201);
    		$view->setHeader("Location", $this->generateUrl("get_file_content", array("id"=>$file->getId())));
    	} catch (Exception $e) {
    		$view->setStatusCode(500);
    		$view->setData($e);
    	}

    	return $this->handleView($view);
    }


    /**
     * @Route("/api/v1/files/{id}", name="file_delete", requirements = {"id" = "\d+"})
     * @Method({"DELETE"})
     * @ParamConverter("file", class="AppBundle:File", options = {"repository_method" = "findNotDeleted"})
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
        $path = $this->get("kernel")->getRootDir() . DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR . "uploads";

        try {
        	if ("" != $file->getInternalFilename() && file_exists($path . DIRECTORY_SEPARATOR . $file->getInternalFilename())) {
        		unlink($path . DIRECTORY_SEPARATOR . $file->getInternalFilename());
        	}

        	$file->setDeletedAt(date_create_from_format("Y-m-d H:i:s", date("Y-m-d H:i:s")));
            $repo = $this->getDoctrine()->getManager()->getRepository("AppBundle:File");
			$repo->update($file);

            $view->setStatusCode(204);
        } catch (Exception $e) {
            $view->setData(array("message" => $e->getMessage()));
            $view->setStatusCode(500);
        }

        return $this->handleView($view);
    }


    /**
     * @Route("/api/v1/files/{id}", name="file_replace_metadata", requirements = {"id" = "\d+"})
     * @Method({"PUT"})
     * @ParamConverter("file", class="AppBundle:File", options = {"repository_method" = "findNotDeleted"})
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
    	$view = new View();
        $path = $this->get("kernel")->getRootDir() . DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR . "uploads";

        $file->setFilename($request->get("filename"))
        ->setStatus((integer) $request->get("status"))
        ->setUpdatedAt(date_create_from_format("Y-m-d H:i:s", date("Y-m-d H:i:s")));

        $validator = $this->get("validator");
        $errors = $validator->validate($file);

        if (count($errors)) {
            $view->setStatusCode(400);
            $view->setData($errors);
        } else {
            try {
                $repo = $this->getDoctrine()->getManager()->getRepository("AppBundle:File");
				$repo->update($file);

                $view->setData($file);
                $view->setStatusCode(200);
            } catch (\Doctrine\DBAL\DBALException $e) {
                $view->setStatusCode(500);
                $view->setData($e->getMessage());
            }
        }

        return $this->handleView($view);
    }



    /**
     * @Route("/api/v1/files/{id}", name="file_update_metadata", requirements = {"id" = "\d+"})
     * @Method({"PATCH"})
     * @ParamConverter("file", class="AppBundle:File", options = {"repository_method" = "findNotDeleted"})
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
    	$view = new View();

        $oldname = $file->getFilename();
        $path = $this->get("kernel")->getRootDir() . DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR . "uploads";

        if ($request->request->has("filename")) {
        	$file->setFilename($request->request->get("filename"));
        }

        if ($request->request->has("status")) {
        	$file->setStatus((integer) $request->request->get("status"));
        }

        $file->setUpdatedAt(date_create_from_format("Y-m-d H:i:s", date("Y-m-d H:i:s")));

        $validator = $this->get("validator");
        $errors = $validator->validate($file);

        if (count($errors)) {
            $view->setStatusCode(400);
            $view->setData($errors);
        } else {
            try {
                $repo = $this->getDoctrine()->getManager()->getRepository("AppBundle:File");
				$repo->update($file);
                $view->setData($file);
                $view->setStatusCode(200);
            } catch (\Doctrine\DBAL\DBALException $e) {
                $view->setStatusCode(500);
                $view->setData($e->getMessage());
            }
        }

        return $this->handleView($view);
    }



    /**
     * @Route("/api/v1/files/{id}/content", name="file_replace_content", requirements = {"id" = "\d+"})
     * @Method({"PUT"})
     * @ParamConverter("file", class="AppBundle:File", options = {"repository_method" = "findNotDeleted"})
     * @ApiDoc(
     *     description = "Replace file content based on ID",
     *     parameters = {
     *         {"name" = "file", "dataType" = "file", "required" = true, "description" = "File to be uploaded"}
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
    public function updateFileContentAction(Request $request, File $file)
    {
    	$view = new View();
    	$path = $this->get("kernel")->getRootDir() . DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR . "uploads";
    	$uploaded = $request->files->get("file");
    	$newFilename = $this->generateUniqueInternalFilename();

    	if ("" != $file->getInternalFilename() && file_exists($path . DIRECTORY_SEPARATOR . $file->getInternalFilename())) {
    		unlink($path . DIRECTORY_SEPARATOR . $file->getInternalFilename());
    	}

    	try {
    		$file->setFilename($uploaded->getClientOriginalName());
    		$file->setInternalFilename($newFilename);
    		$repo = $this->getDoctrine()->getManager()->getRepository("AppBundle:File");
			$repo->update($file);

    		$uploaded->move($path , $newFilename);
    		$view->setStatusCode(204);
    	} catch (Exception $e) {
    		$view->setStatusCode(500);
    		$view->setData($e);
    	}

    	return $this->handleView($view);
    }


    public function generateUniqueInternalFilename(){
    	return bin2hex(openssl_random_pseudo_bytes(64));
    }

}
