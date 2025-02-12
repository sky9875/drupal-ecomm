<?php

namespace Drupal\custom_module\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\Request;
use Drupal\file\Entity\File;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Controller for custom file upload functionality.
 */
class CustomController extends ControllerBase {

  /**
   * Handles file upload and renaming.
   */
  public function uploadFile(Request $request) {
    // Get the uploaded file from the request (assuming the file is passed as 'file').
    $file = $request->files->get('file');

    if (!$file) {
      return new JsonResponse(['error' => 'No file uploaded'], 400);
    }

    // Save the file to the Drupal file system.
    $file = file_save_data(file_get_contents($file->getPathname()), 'public://uploads/' . $file->getClientOriginalName(), FILE_EXISTS_REPLACE);
    if (!$file) {
      return new JsonResponse(['error' => 'File could not be saved'], 500);
    }

    // Call the function to clean the file name.
    $new_filename = custom_module_clean_filename($file->getFilename());

    // If the filename has changed, update the file name.
    if ($file->getFilename() !== $new_filename) {
      $file->setFilename($new_filename);
      $file->save();
    }

    // Return a success response with the new filename.
    return new JsonResponse(['message' => 'File uploaded successfully', 'filename' => $new_filename]);
  }
}
