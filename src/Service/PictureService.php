<?php

namespace App\Service;

use Exception;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class PictureService
{
    private $params;

    public function __construct(ParameterBagInterface $params)
    {
        $this->params = $params;
    }

    public function add(UploadedFile $picture, string $caseReference , ?string $folder = '', ?int $width = 500, ?int $height = 500)
    {
        // Generate a unique filename
        $file = uniqid($caseReference).'.'.$picture->getClientOriginalExtension();

        // Get image information
        $pictureInfos = getimagesize($picture);

        if ($pictureInfos === false) {
            return new JsonResponse(['error'=>'Picture format is incorrect'], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        // Check the picture format
        switch ($pictureInfos['mime']) {
            case 'image/png':
                $pictureSource = imagecreatefrompng($picture);
                break;
            case 'image/jpeg':
                $pictureSource = imagecreatefromjpeg($picture);
                break;
            case 'image/webp':
                $pictureSource = imagecreatefromwebp($picture);
                break;
            default:
                return new JsonResponse(["error" => "Le format attendu ne correspond pas"], Response::HTTP_UNSUPPORTED_MEDIA_TYPE, []);
        }

        // Determine the image dimensions and cropping coordinates
        $imageWidth = $pictureInfos[0];
        $imageHeight = $pictureInfos[1];
        switch ($imageWidth <=> $imageHeight) {
            case -1: // Portrait
                $squareSize = $imageWidth;
                $srcX = 0;
                $srcY = ($imageHeight - $squareSize) / 2;
                break;
            case 0: // Square
                $squareSize = $imageWidth;
                $srcX = 0;
                $srcY = 0;
                break;
            case +1: // Landscape
                $squareSize = $imageHeight;
                $srcX = ($imageWidth - $squareSize) / 2;
                $srcY = 0;
                break;
        }

        // Define the target folder
        $path = $this->params->get('images_directory') . $folder;

        // Create the recipient folder if it doesn't exist
        if (!file_exists($path . '/mini/')) {
            mkdir($path . '/mini/', 0755, true);
        }

        // Create a new empty picture with the specified width and height
        $resizedPicture = imagecreatetruecolor($width, $height);

        // Resize the image and copy it to the resized picture
        imagecopyresampled(
            $resizedPicture,
            $pictureSource,
            0, 0, $srcX, $srcY,
            $width, $height,
            $squareSize, $squareSize
        );

        // Save the resized image
        imagejpeg($resizedPicture, $path . '/mini/' . $width . 'x' . $height . '-' . $file);

        // Save the original image (without resizing)
        copy($picture->getPathname(), $path . '/' . $file);

        // Free up memory
        imagedestroy($resizedPicture);

        return $file;
    }


        /**
         * Deletes an image from the specified folder.
         *
         * @param string $file The filename of the image to delete.
         * @param string|null $folder The folder where the image is stored (default is an empty string).
         * @param int|null $width The width of the image (default is 500).
         * @param int|null $height The height of the image (default is 500).
         *
         * @return bool Returns true if the image was successfully deleted; otherwise, false.
         */
        public function delete(string $file, ?string $folder = '', ?int $width = 500, ?int $height = 500)
        {
            if($file !== 'default.jpeg') {
                $success = false;
                $path = $this->params->get('images_directory') . $folder;

                $mini = $path . '/mini/' . $width . 'x' . $height . '-' . $file;
                if(file_exists($mini)) {
                    unlink($mini);
                    $success = true;
                }
                $original = $path . '/' . $file;

                if(file_exists($original)) {
                    unlink($original);
                    $success = true;

                    return $success;
                }
            return false;
        }


    }

}