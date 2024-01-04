<?php

namespace App\Model;

class OSM extends \Illuminate\Database\Eloquent\Model {

    protected $table = 'osm';
    protected $fillable = array('osmid', 'osmtype');
    protected $appends = array('tagList', 'name');
	protected $allowedFields = ['wheelchair', 'wheelchair:description','toilets:wheelchair','disabled:description','hearing_loop','toilets'];

    public function scopeWhereOSMId($query, $osmtype, $osmid) {
        return $query->where('osmtype', $osmtype)->where('osmid', $osmid);
    }

    public function tags() {
        return $this->hasMany(\App\Model\OSMTag::class, 'osmid', 'id')->orderBy('name');
    }

    public function churches() {
        return $this->belongsToMany(\App\Model\Church::class, 'lookup_church_osm', 'osm_id', 'church_id');
    }

    public function getNameAttribute($value) {
        $order = ['name:hu', 'name', 'alt_name:hu', 'alt_name', 'old_name:hu', 'old_name'];
        foreach ($order as $key) {
            if (array_key_exists($key, $this->tagList)) {
                return $this->tagList[$key];
            }
        }
        return false;
    }

    public function getAdministrativeAttribute($value) {
        for ($i = 1; $i < 10; $i++) {
            $osm = $this->_getAdministrative($i);
            if ($osm AND $osm->name) {
                $return[$i] = $osm;
            }
        }
        return $return;
    }

    public function _getAdministrative($level) {
        return $this->enclosing()
                        ->whereHasTag('boundary', 'administrative')
                        ->whereHasTag('admin_level', $level)
                        ->first();
    }

    public function getAddressAttribute($value) {
        $return = new \stdClass();
        if (array_key_exists('addr:street', $this->tagList)) {
            $return->name = $this->tagList['addr:street'] . " " . $this->tagList['addr:housenumber'];
        }
        return $return;
    }

    public function getReligiousAdministrationAttribute($value) {
        $administrationLevels = ['diocese', 'deaconry', 'parish'];
        $return = false;
        foreach ($administrationLevels as $level) {
            if ($this->$level) {
                $return[$level] = $this->$level;
            }
        }
        return $return;
    }

    public function getDioceseAttribute($value) {
        return $this->enclosing()
                        ->whereHasTag('boundary', 'religious_administration')
                        ->whereHasTag('admin_level', '6')
                        ->first();
    }

    public function getDeaconryAttribute($value) {
        return $this->enclosing()
                        ->whereHasTag('boundary', 'religious_administration')
                        ->whereHasTag('admin_level', '7')
                        ->first();
    }

    public function getParishAttribute($value) {
        return $this->enclosing()
                        ->whereHasTag('boundary', 'religious_administration')
                        ->whereHasTag('admin_level', '8')
                        ->first();
    }

    public function scopeWhereHasTag($query, $name, $arg2, $arg3 = false) {
        if ($arg2 == false) {
            $value = $arg2;
            $operator = '=';
        } else {
            $value = $arg3;
            $operator = $arg2;
        }
        return $query->whereHas('tags', function ($query) use ($name, $operator, $value) {
                    $query->where('name', $name)->where('value', $operator, $value);
                });
    }

    public function getTagListAttribute($value) {
        $return = [];
		$tags = \App\Model\OSMTag::where('osmtype',$this->osmtype)
                ->where('osmid',$this->osmid)                
                ->get();
				
		foreach ($tags as $tag) {
            $return[$tag->name] = $tag->value;
        }
        return $return;
    }

	/* Update all tags from OSM's Overpass API */
	function updateFromOverpass() {
		$overpass = new \App\ExternalApi\OverpassApi();
		//$overpass->cache = false; // We need the fresh data
		$overpass->query = $this->osmtype."(id:".$this->osmid.");out tags qt center;";
		$overpass->run();
		$overpass->saveElement();
		$this->fresh(); 	/* Be aware that you need ->fresh() to reload the data */
		return true;
	}
	
	public function fresh($with = [])
	{
		$key = $this->getKeyName();
		return $this->exists ? static::with($with)->where($key, $this->getKey())->first() : null;
	}
	
    //Returns OSM elements that encloses this element. This element enclosed by the returning ones.
    public function enclosing() {
        return $this->belongsToMany(\App\Model\OSM::class, 'lookup_osm_enclosed', 'osm_id', 'enclosing_id');
    }

    //Return OSM elements that is enclosed by this element. This element encloses the returning ones.
    public function enclosed() {
        return $this->belongsToMany(\App\Model\OSM::class, 'lookup_osm_enclosed', 'enclosing_id', 'osm_id');
    }
		
    public function delete() {
        $this->tags()->delete();
        parent::delete();
    }

	/* Create a changeset and upload to OSM */
	/* TODO: it works only with tags yet. No lat, lon, and anything else */
	function upload() {
		global $config;
		if( $config['openstreetmap'] == false ) {
			addMessage('Az OpenStreetMap API nincs bekapcsolva. Így az esetleges OSM adat változásokat nem töltöttük fel.','warning');
			return;
		}
	
	
		$tags = \App\Model\OSMTag::where('osmtype',$this->osmtype)
                ->where('osmid',$this->osmid)                
                ->get()->keyBy('name')->toArray();
	
		//$this->osmtype = "node";
		//$this->osmid = "4332337979";
	
		//Get the exact data we need to change
		$osm = new \App\ExternalApi\OpenstreetmapApi();
		$osm->cache = false;
		$osm->query = $this->osmtype."/".$this->osmid;
		if(!$osm->run()) return false;
		
		$xmlData = $osm->xmlData;
				
		
		//Add node/way/relation to the changeset
		//TODO: ellenőrizni, hogy van-e változás, vagy felküldjük agresszíven
		//TODO: üreseket nem beküldeni? vagy törölni? vagy mi van?
		$elements = $xmlData->xpath("//".$this->osmtype."[@id='".$this->osmid."']");
		$element = $elements[0];

		$remove = [];
		$update = false;
		foreach($this->allowedFields as $field) {
			if(array_key_exists($field,$tags)) {
					$tag = $element->xpath("tag[@k='".$field."']");
					//Ha már létezik korábbról
					if(count($tag) > 0 ) {
						//Ha változott
						if($tags[$field]['value'] != $tag[0]->attributes()->v ) {
							$tag[0]->attributes()->v = $tags[$field]['value'];
							$update = true;
							
							if($tag[0]->attributes()->v == '') {
								//az unset meg remove nem működik :(
								$remove[] = $tag[0]->asXML();
							} 
						}
					//Ha nem létezik korábbról ÉS van azért valami értéke
					} elseif ( $tags[$field]['value'] != '' )  {
						$tag[0] = $element->addChild('tag');
						$tag[0]->addAttribute('k',$field);
						$tag[0]->addAttribute('v',$tags[$field]['value']);
						$update = true;
					}
										
					
			}
		}
				
		if($update == true) {
				
			//Open empty changeset
			//TODO: created_by és comment és egyebek
			$osm = new \App\ExternalApi\OpenstreetmapApi();
			$osm->cache = false;		
			$changeset = $osm->prepareNewChangeset();
			$osm->curl_setopt(CURLOPT_USERPWD, $osm->userpwd);
			$osm->query = "changeset/create";
			$osm->format = 'text';
			$osm->curl_setopt(CURLOPT_CUSTOMREQUEST ,"PUT");		 	
			$osm->curl_setopt(CURLOPT_POSTFIELDS,$changeset->asXML());
			$osm->run();
			$changesetID = (int) $osm->rawData;
			
			$xmlData->{$this->osmtype}[0]->attributes()->changeset = $changesetID;	
			$xmlString = $xmlData->asXML();
			
			$xmlString = str_replace($remove, '', $xmlString); //Sajnos kell ez.
			
			$osm = new \App\ExternalApi\OpenstreetmapApi();
			$osm->cache = false;		
			$osm->query = $this->osmtype."/".$this->osmid;
			$osm->curl_setopt(CURLOPT_USERPWD, $osm->userpwd);
			$osm->curl_setopt(CURLOPT_CUSTOMREQUEST ,"PUT");		 	
			$osm->format = 'text';
			$osm->curl_setopt(CURLOPT_POSTFIELDS,$xmlString);
			$osm->run();
			$versionID = (int) $osm->rawData;		
			
			//Close changeset
			$osm = new \App\ExternalApi\OpenstreetmapApi();
			$osm->cache = false;		
			$osm->curl_setopt(CURLOPT_USERPWD, $osm->userpwd);
			$osm->query = "changeset/".$changesetID."/close";
			$osm->format = 'text';
			$osm->curl_setopt(CURLOPT_CUSTOMREQUEST ,"PUT");		 			
			$osm->run();
			
			$messageurl = $osm->apiUrl."changeset/".$changesetID; 
			
			addMessage ('Közvelenül OSM adatokat is módosítottunk. Nagyon izgalmas. <a href="'.$messageurl.'">changeset/'.$changesetID.'</a>','success');
		}
	}
}
