<?php

namespace Html;

use Illuminate\Database\Capsule\Manager as DB;

class UploadImage extends Html {

    public function __construct($path) {
        $this->tid = $path[0];
        $this->church = \Eloquent\Church::find($this->tid);
        $this->pageDescription = 'új kép feltöltése';

        // Get PHP upload limits
        $this->uploadLimits = $this->getUploadLimits();

        if (isset($_REQUEST['upload'])) {
            $this->ajax();
            exit;
        }
    }

    private function getUploadLimits() {
        // Get various PHP upload settings
        $upload_max_filesize = ini_get('upload_max_filesize');
        $post_max_size = ini_get('post_max_size');
        $memory_limit = ini_get('memory_limit');
        $max_file_uploads = ini_get('max_file_uploads');
        
        // Convert to bytes for comparison
        $upload_max_bytes = $this->convertToBytes($upload_max_filesize);
        $post_max_bytes = $this->convertToBytes($post_max_size);
        $memory_limit_bytes = $this->convertToBytes($memory_limit);
        
        // The effective limit is the smallest of upload_max_filesize and post_max_size
        $effective_limit_bytes = min($upload_max_bytes, $post_max_bytes);
        
        $final_limit_bytes = $effective_limit_bytes;
        
        return [
            'upload_max_filesize' => $upload_max_filesize,
            'upload_max_bytes' => $upload_max_bytes,
            'post_max_size' => $post_max_size,
            'post_max_bytes' => $post_max_bytes,
            'memory_limit' => $memory_limit,
            'memory_limit_bytes' => $memory_limit_bytes,
            'max_file_uploads' => $max_file_uploads,
            'effective_limit_bytes' => $effective_limit_bytes,
            'final_limit_bytes' => $final_limit_bytes,
            'final_limit_mb' => round($final_limit_bytes / 1024 / 1024, 2)
        ];
    }
    
    private function convertToBytes($val) {
        $val = trim($val);
        $last = strtolower($val[strlen($val)-1]);
        $val = (int) $val;
        switch($last) {
            case 'g':
                $val *= 1024;
            case 'm':
                $val *= 1024;
            case 'k':
                $val *= 1024;
        }
        return $val;
    }

    function ajax() {
        try {
            $tid = $_POST['id'];
            if ($tid != $this->tid) {
                throw new \Exception("The church.id of the page and the form are not the same.");
            }

            $photo = new \Eloquent\Photo();
            $photo->church_id = $this->church->id;
            $photo->uploadFile($_FILES["FileInput"]);

            $photo->title = htmlspecialchars($_REQUEST['description']);
            $photo->save();
            
            // Set JSON response header
            header('Content-Type: application/json');
            
            // Prepare success response
            $response = [
                'success' => true,
                'message' => 'Siker! Feltöltöttük. Jöhet a következő!',
                'image_url' => $photo->smallUrl,
                'photo_id' => $photo->id,
                'html' => "Siker! Feltöltöttük. Jöhet a következő!<br/><img src='" . $photo->smallUrl . "' class='img-thumbnail' style='max-width: 200px;'>"
            ];

            $this->photo = $photo;
        
        
        /*
         * miserend adminiok
         * egyházmegyei felelős(ök)
         * templom feltöltésre jogosult felhasználó
         */
        $emails = [];        
        /* Miserend Adminok */
        $admins = DB::table('user')->where('jogok','LIKE','%miserend%')->where('notifications',1)->get();
        foreach($admins as $admin) {
           $emails[$admin->email] = ['image_admin',$admin->email,$admin];
        }              
        /* Egyházmegyei felelős (csak felhasználónév alapján) */
        $responsabile = DB::table('egyhazmegye')->select('user.*')->where('egyhazmegye.id',$this->church->egyhazmegye)->leftJoin('user','user.login','=','egyhazmegye.felelos')->where('notifications',1)->first();
        if($responsabile) {
            $emails[$responsabile->email] = ['image_diocese', $responsabile->email, $responsabile];
        }
        /* Templom felelősök */
        $churchHolders = DB::table('church_holders')->where('church_id',$this->church->id)->where('church_holders.status','allowed')->leftJoin('user','user.uid','=','church_holders.user_id')->where('user.notifications',1)->get();        
        foreach($churchHolders as $churchHolder) {
            $emails[$churchHolder->email] = ['image_responsible', $churchHolder->email, $churchHolder];
        }
        
        foreach($emails as $email) {
            if(isset($email[2])) $this->addressee = $email[2];
            else $this->addressee = false;
            $mail = new \Eloquent\Email();                
            $mail->render($email[0],$this);
            $mail->send($email[1]);
        }
        
        // Send JSON response
        echo json_encode($response);
        exit;
        
        } catch (\Exception $e) {
            // Set JSON response header for errors too
            header('Content-Type: application/json');
            http_response_code(400); // Changed from 500 to 400 (Bad Request)
            
            $errorResponse = [
                'success' => false,
                'error' => true,
                'text' => $e->getMessage(),
                'message' => 'Hiba történt a feltöltés során: ' . $e->getMessage(),
                'debug_info' => [
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'trace' => $e->getTraceAsString()
                ]
            ];
            
            // Log the error for debugging
            error_log("HTML Upload Error: " . $e->getMessage() . " in " . $e->getFile() . ":" . $e->getLine());
            
            echo json_encode($errorResponse);
            exit;
        }
    }

}
