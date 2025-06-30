<?php

namespace Api;

class Upload extends Api {

    public $requiredFields = array('tid','photo');
    public $photo; // Photo object

    public function validateVersion() {
        if ($this->version < 4) {
            throw new \Exception("API action 'upload' is not available under v4.");
        }
    }

    public function validateInput() {
        //TODO: !isValidChurchId()?
        if (!is_numeric($this->input['tid'])) {
            throw new \Exception("Wrong format of 'tid' in JSON input.");
        }    
    }

    public function run() {
        parent::run();
        $this->getInputJson();
        
        //Decoda and Validate ImageData
        $image = $this->decodeImage($this->input['photo']);
        
        $imagesize = getimagesize($image['tmp_name']);
        if(!$imagesize) {
            throw new \Exception("File is not a valid image.");
        }
        
        $this->photo = new \Eloquent\Photo();
        $_SERVER['HTTP_X_REQUESTED_WITH'] = true; //I do not know why I need it. See Eloquent/photo.php
        $this->photo->church_id = $this->input['tid'];
        $this->photo->flag = 'i'; // Set default flag as 'i' (inactive/pending)
        $this->photo->weight = 0; // Set default weight
        $this->photo->uploadFile($image);
        
        // Additional debug info in response
        $this->return['text'] = 'Köszönjük a képet. Elmentettük.';
        $this->return['photo_id'] = $this->photo->id;
        $this->return['church_id'] = $this->photo->church_id;
        $this->return['filename'] = $this->photo->filename;
    }
    
    private function decodeImage($data) {
        if (preg_match('/^data:image\/(\w+);base64,/', $data, $type)) {
            $data = substr($data, strpos($data, ',') + 1);
            $imgtype = strtolower($type[1]); // jpg, png, gif

            if (!in_array($imgtype, [ 'jpg', 'jpeg', 'gif', 'png' ])) {
                throw new \Exception('invalid image type: ' . $imgtype);
            }

            $data = base64_decode($data);

            if ($data === false) {
                throw new \Exception('base64_decode failed');
            }
            
            try {
                $tmp_name = tempnam(sys_get_temp_dir(), 'FOO'); 
                file_put_contents($tmp_name, $data);
            }
            catch(\Exception $e){
                throw new \Exception("Temporary file cannot saved.");
            }
            
            // Convert the short image type to MIME type for compatibility
            $mime_type = 'image/' . ($imgtype === 'jpg' ? 'jpeg' : $imgtype);
            
            return array(
                'name' => 'temporaryImageFile.' . $imgtype,
                'type' => $mime_type,
                'tmp_name' => $tmp_name,
                'error' => 0,
                'size' => filesize($tmp_name)
            );
        } else {
            throw new \Exception('did not match data with image data');
        }        
    }

}
