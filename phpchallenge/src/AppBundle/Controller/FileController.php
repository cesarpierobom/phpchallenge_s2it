<?php

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Swagger\Annotations as SWG;

class FileController extends Controller
{
    /**
     * @Route("/files", name="file_list", methods = {"GET"})
     * @ApiDoc(
     *     resource = true,
     *     description = "Returns all the files that the user has access to",
     *     filters={
     *         {"name" = "filename", "dataType" = "string", "description" = "Filter files by filename"}
     *     },
     *     output = {"collection" = true, "collectionName" = "files", "class" = "AppBundle\Entity\File"},
     *     statusCodes={
     *         200 = "Returned when successful"
     *     }
     * )
     */
    public function listAction()
    {

    }


    /**
     * @Route("/files/{file}", name = "file_show", requirements = {"file" = "\d+"}, methods = {"GET"})
     * @ApiDoc(
     *     description = "Returns file metadata based on its ID",
     * )
     * */
    public function showFileAction($file)
    {

    }



    /**
     * @Route("/files/{file}/content", name="file_content", requirements={"file" = "\d+"}, methods = {"GET"})
     * @ApiDoc(
     *     description = "Returns the file content based on its ID",
     * )
     * */
    public function getFileContentAction($file)
    {

    }


    /**
     * @Route("/files", name="file_store", methods = {"POST"})
     * @ApiDoc(
     *     description = "Inserts new file metadata in the database",
     * )
     * */
    public function storeFileAction()
    {

    }


    /**
     * @Route("/files/{file}/content", name="file_content_store", methods = {"POST"})
     * @ApiDoc(
     *     description = "Uploads the file contents to the storage based on its ID",
     * )
     * */
    public function storeFileContentAction($file)
    {

    }


    /**
     * @Route("/files/{file}", name="file_delete", methods = {"DELETE"})
     * @ApiDoc(
     *     description = "Deletes a file from the database and its contents from the storage",
     * )
     * */
    public function deleteFileAction()
    {

    }


    /**
     * @Route("/files/{file}", name="file_replace_metadata", methods = {"PUT"})
     * @ApiDoc(
     *     description = "Completely replaces/updates file metadata based on ID",
     * )
     * */
    public function replaceFileMetadataAction()
    {

    }



    /**
     * @Route("/files/{file}", name="file_update_metadata", methods = {"PATCH"})
     * @ApiDoc(
     *     description = "Partialy updates file metadata based on ID",
     * )
     * */
    public function patchFileMetadataAction()
    {

    }



    /**
     * @Route("/files/{file}/content", name="file_replace_content", methods = {"PUT"})
     * @ApiDoc(
     *     description = "Replace file content based on ID",
     * )
     * */
    public function updateFileContentAction()
    {

    }

}
