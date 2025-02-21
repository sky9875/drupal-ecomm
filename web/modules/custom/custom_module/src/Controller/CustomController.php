<?php

namespace Drupal\custom_module\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Drupal\file\Entity\File;
use Drupal\Core\Url;

/**
 * Controller for handling file uploads and other functionalities.
 */
class CustomController extends ControllerBase {

  /**
   * Handles image upload and renaming to UUID.
   */
  public function uploadImage(Request $request) {
    // Get the uploaded image file.
    $file = $request->files->get('image');
  
    if (!$file) {
      return new JsonResponse(['error' => 'No file uploaded'], 400);
    }
  
    // Get the file contents.
    $file_data = file_get_contents($file->getPathname());
    $extension = pathinfo($file->getClientOriginalName(), PATHINFO_EXTENSION);
    
    // Validate file extension.
    $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif'];
    if (!in_array(strtolower($extension), $allowed_extensions)) {
      return new JsonResponse(['error' => 'Invalid file type. Only images are allowed.'], 400);
    }
  
    // Generate a UUID for the new file name.
    $uuid = \Drupal::service('uuid')->generate();
    $new_filename = $uuid . '.' . $extension;
    error_log('Generated UUID filename: ' . $new_filename); // Debug: Log the new filename
  
    // Ensure the directory exists.
    $directory = DRUPAL_ROOT . '/sites/default/files/uploads/';
    if (!file_exists($directory)) {
      if (!mkdir($directory, 0777, true) && !is_dir($directory)) {
        return new JsonResponse(['error' => 'Failed to create directory for uploads'], 500);
      }
    }
    $destination = $directory . $new_filename;
    error_log('Destination path: ' . $destination); // Debug: Log the destination path
  
    // Save the file with the new UUID filename.
    try {
      file_put_contents($destination, $file_data);
    } catch (\Exception $e) {
      return new JsonResponse(['error' => 'An error occurred while saving the file: ' . $e->getMessage()], 500);
    }
  
    if (!file_exists($destination)) {
      return new JsonResponse(['error' => 'File could not be saved'], 500);
    }
  
    // Return the new filename in a JSON response.
    return new JsonResponse([
      'message' => 'Image uploaded successfully',
      'filename' => $new_filename
    ]);
  }
  
  // Other methods in your controller, if needed...
}
