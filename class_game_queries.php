<?php
/**
 * File: 		class_game_queries.php
 * Purpose:		Object class that uses the game id to query for all the necessary information for the thumbnail
 * Author:		Toua Vang
 * Data:		4/5/2012
 */


class class_game_queries {
	
	//Declare variables
	private $id = false;
	private $thumbnail = false;
	private $name = false;
	private $description = false;
	private $genre = false;
	private $authors = false;
	private $semester = false;
	private $inception = false;
	private $environment = false;
	private $year = false;
		
	/** 
	 * Constructor that needs an id to query for all the necessary information and stores them into their variables
	 */
	function __construct($g_id){

	}

	/**
 	* Function that loop through all the possible values from the result 
 	* and store them into one variable
 	*
 	* Return string
 	*/
	public function fetchResult($typeResult){
		$resultValue="";
		if(is_bool($typeResult) ==false)
		{
			$rowResult = mysqli_fetch_row($typeResult);
			do{
				$resultValue=$resultValue.$rowResult[0];
				if($rowResult = mysqli_fetch_row($typeResult))
					$resultValue=$resultValue.", ";
			
			} while ($rowResult);
		}
		return $resultValue;
	}

	/**
 	* Function that creates an associated array from the result
 	*
 	* Return array
 	*/
	function fetchResultArray($typeResult){
		if(is_bool($typeResult) ==false)
		{
			$arrResult = mysqli_fetch_assoc($typeResult);
			//var_dump($arrResult); //***********testing
			return $arrResult;
		}
	}
	
}

?>
