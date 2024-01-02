<?php

namespace Html\Church;

class EditPhotos extends \Html\Html {

    public function __construct($path) {
        global $user;
   
        $this->input = $_REQUEST;
        $this->tid = $path[0];
        $this->church = \Eloquent\Church::find($this->tid);
        if (!$this->church) {
            throw new \Exception('Nincs ilyen templom.');
        }
        $this->church = $this->church->append(['writeAccess']);

        if (!$this->church->writeAccess) {
            throw new \Exception('Hiányzó jogosultság!');
            return;
        }

		 $allapotok = \Eloquent\Remark::where('church_id',$this->tid)->groupBy('allapot')->pluck('allapot')->toArray();            
            if (in_array('u', $allapotok))
				$this->church->remarks_icon = "ICONS_REMARKS_NEW";                
            elseif (in_array('f', $allapotok))
				$this->church->remarks_icon = "ICONS_REMARKS_PROCESSING";
            elseif (count($allapotok) > 0)
				$this->church->remarks_icon = "ICONS_REMARKS_ALLDONE";
			else
				$this->church->remarks_icon = "ICONS_REMARKS_NO";
		
        $isForm = \Request::Text('submit');
        if ($isForm) {
            $this->modify();
        }
        
		$this->church->photos;
        $this->title = $this->church->fullName;
		
    }

    function modify() {
        if ($this->input['church']['id'] != $this->tid) {
            throw new \Exception("Gond van a módosítandó templom azonosítójával.");
        }

   
        if (isset($this->input['photos'])) {
            foreach ($this->input['photos'] as $modPhoto) {
                $origPhoto = \Eloquent\Photo::find($modPhoto['id']);
                if ($origPhoto) {
                    if ($modPhoto['flag'] == 'i')
                        $origPhoto->flag = 'i';
                    else
                        $origPhoto->flag = "n";
                    if ($modPhoto['weight'] == '' OR is_numeric((int) $modPhoto['weight']))
                        $origPhoto->weight = $modPhoto['weight'];
                    else
                        $origPhoto->order = 0;
                    $origPhoto->title = $modPhoto['title'];
                    $origPhoto->save();
                    if (isset($modPhoto['delete'])) {
                        $origPhoto->delete();
                    }
                }
            }
        }

        global $user;
        $this->church->log .= "\nFotók: " . $user->login . " (" . date('Y-m-d H:i:s') . ")";
               
        switch ($this->input['modosit']) {
            case 'n':
                $this->redirect("/church/catalogue");
                break;

            case 't':
                $this->redirect("/church/" . $this->church->id);
                break;

            case 'm':
                $this->redirect("/church/" . $this->church->id . "/editschedule");
                break;

            default:
                break;
        }
    }

  

    
}
