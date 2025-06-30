<?php

namespace Html;

use Illuminate\Database\Capsule\Manager as DB;

class Apitest extends Html {

    public $sampleChurchId;
    public $sampleImageBase64;
    public $sampleImage; // For template compatibility

    public function __construct() {
        parent::__construct();
        $this->setTitle('API tesztelés');

        global $user;
        if (!$user->isadmin) {
            addMessage("Hozzáférés megtagadva!", "danger");
            $this->redirect('/');
        }

        // Get a sample church ID for testing
        $this->sampleChurchId = 1;
        
        // Create a sample base64 image for testing
        $this->sampleImageBase64 = $this->createSampleImage();
        $this->sampleImage = $this->sampleImageBase64; // For template compatibility
    }
    
    private function createSampleImage() {
        // Create a simple 100x100 red square as a sample image
        if (!extension_loaded('gd')) {
            return 'data:image/jpeg;base64,/9j/4AAQSkZJRgABAQEAYABgAAD/2wBDAAEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQH/2wBDAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQH/wAARCAABAAEDASIAAhEBAxEB/8QAFQABAQAAAAAAAAAAAAAAAAAAAAv/xAAUEAEAAAAAAAAAAAAAAAAAAAAA/8QAFQEBAQAAAAAAAAAAAAAAAAAAAAX/xAAUEQEAAAAAAAAAAAAAAAAAAAAA/9oADAMBAAIRAxEAPwA/wA=='; // minimal 1x1 JPEG
        }
        
        try {
            // Create a 100x100 image
            $image = imagecreate(100, 100);
            
            // Set colors
            $red = imagecolorallocate($image, 255, 0, 0);
            $white = imagecolorallocate($image, 255, 255, 255);
            
            // Fill background with red
            imagefill($image, 0, 0, $red);
            
            // Add some text
            imagestring($image, 3, 10, 10, 'TEST', $white);
            imagestring($image, 2, 10, 30, 'API', $white);
            imagestring($image, 2, 10, 50, 'Upload', $white);
            
            // Capture output
            ob_start();
            imagejpeg($image, null, 80);
            $imageData = ob_get_contents();
            ob_end_clean();
            
            // Clean up memory
            imagedestroy($image);
            
            // Return base64 encoded data URI
            return 'data:image/jpeg;base64,' . base64_encode($imageData);
            
        } catch (\Exception $e) {
            // Fallback to a minimal base64 image if GD fails
            return 'data:image/jpeg;base64,/9j/4AAQSkZJRgABAQEAYABgAAD/2wBDAAEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQH/2wBDAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQEBAQH/wAARCAABAAEDASIAAhEBAxEB/8QAFQABAQAAAAAAAAAAAAAAAAAAAAv/xAAUEAEAAAAAAAAAAAAAAAAAAAAA/8QAFQEBAQAAAAAAAAAAAAAAAAAAAAX/xAAUEQEAAAAAAAAAAAAAAAAAAAAA/9oADAMBAAIRAxEAPwA/wA==';
        }
    }
}
