<?php

namespace Api;

use Illuminate\Database\Capsule\Manager as DB;

class Database extends Api {

    public $format = 'text'; //or text

        
    public function validateVersion() {
        if ($this->version < 4) {
            throw new \Exception("API action 'nearby' is not available under v4.");
        }
    }

    public function run() {
        parent::run();
				
		// Csakis üresen szabad lementeni, mert érzékeny adatok.
		$this->tabels_to_truncate = ['chat', 'church_holders', 'favorites', 'emails', 'remarks', 'user'];
		// Automatikusan töltődnek, ezért elég üresen. Bár van hogy lassan töltődnek...
		$this->tabels_to_truncate = array_merge($this->tabels_to_truncate,
		['boundaries', 'distances', 'lookup_boundary_church', 'messages', 'osm', 'osmtags', 'stats_externalapi', 'tokens']);
						
		// Vannak táblák, amikre már rég nincsen szükség. Ha véletlen nem lenne törölve.
		// Később törlendő innen		
		$this->tabels_to_omit = ['events', 'igenaptar', 'lnaptar', 'modulok', 'nevnaptar', 'oldalkeret', 'szentek', 'unnepnaptar', 'updates', 'osm_tags', 'osmtags', 'osm', 'lookup_osm_enclosed', 'lookup_church_osm'];
				
		// Van egy-két mező, amiben érzékeny adat lehet, ezért nem szabad lementeni
		$this->columns_to_omit = [
			'templomok' => ['adminmegj','kontakt','kontaktmail','log','letrehozta','modositotta'],
			'misek' => ['modositotta', 'torolte']
		];
		
		$tables = DB::select('SHOW TABLES');
		$this->constrains = []; 
		
		foreach( $tables as $table ) {
			$tableName = $table->Tables_in_miserend;
			if(!in_array($tableName, $this->tabels_to_omit)) {
				
				echo $this->generateCreateTableStatement($tableName) . ';' . PHP_EOL . PHP_EOL;;
				
				
				if(!in_array($tableName,$this->tabels_to_truncate)) {
					// Generate the INSERT INTO statements
					$insertStatements = $this->generateInsertStatements($tableName);
					foreach ($insertStatements as $statement) {
						echo $statement . ';' . PHP_EOL;
					}
				}
				
				echo PHP_EOL . PHP_EOL . PHP_EOL;;
			}
		}
	
		foreach ($this->constrains as $constrain) {
			
			echo $constrain .';'. PHP_EOL;
		}
		
		
        return;
    }
    
  
	function generateCreateTableStatement($tableName)
	{
		// Fetch the columns and types
		$tableCreate = DB::select("SHOW CREATE TABLE {$tableName}")[0]->{'Create Table'};
		
		// Legyen benne az "IF NOT EXISTS"
		$tableCreate = preg_replace("/CREATE TABLE `/","CREATE TABLE IF NOT EXISTS `",$tableCreate);
		
		// A CONSTRAINT kiszedjük és áttesszük a lista végére
		preg_match('/  CONSTRAINT(.*)/',$tableCreate,$matches);
		if(isset($matches[0])) {
			$this->constrains[] = "ALTER TABLE `".$tableName."` \n  ADD  ". preg_replace('/,$/', ';', $matches[0]);
			
			$tableCreate = preg_replace('/  CONSTRAINT(.*)/','',$tableCreate);
			$tableCreate = preg_replace( '/,\s*\n\s*\)/',PHP_EOL . ')',$tableCreate);

		}
  	
		return $tableCreate;
	}
		
	function generateInsertStatements($tableName)
	{
		// Fetch all rows from the specified table
		$rows = DB::table($tableName)->get();

		// Fetch column types
		$columnsInfo = DB::select("SHOW COLUMNS FROM {$tableName}");
		$numericTypes = ['int', 'integer', 'smallint', 'tinyint', 'mediumint', 'bigint', 'float', 'double', 'decimal'];

		// Create a map of column types
		$columnTypes = [];
		foreach ($columnsInfo as $columnInfo) {
			$type = explode('(', $columnInfo->Type)[0];
			$columnTypes[$columnInfo->Field] = $type;
		}

		// Generate INSERT INTO statements
		$insertStatements = [];

		foreach ($rows as $row) {
			// Prepare columns and values
			$columns = [];
			$values = [];

			foreach ($row as $column => $value) {
				if ( 
					!isset($this->columns_to_omit[$tableName]) OR !in_array($column,$this->columns_to_omit[$tableName] )  ) {
			
					$columns[] = '`'.$column.'`';				
					if ($column == 'status') {
						$values[] = "'none'";
					} elseif (is_null($value)) {
						$values[] = 'NULL';
					} elseif (in_array($columnTypes[$column], $numericTypes)) {
						$values[] = $value;
					} else {
						$values[] = "'" . addslashes($value) . "'";
					}
				}
			}

			// Create the insert statement
			$insertStatements[] = sprintf(
				"INSERT INTO %s (%s) VALUES (%s);",
				$tableName,
				implode(', ', $columns),
				implode(', ', $values)
			);
		}

		return $insertStatements;
	}
	
	
}
