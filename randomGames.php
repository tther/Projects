<?php

/**
 * File: 		randomGames.php
 * Purpose:		get some random games from DB
 * Author:		Toua Vang
 * Data:		3/23/2012		
 
 * use shuffle
 * bool shuffle(array name)
 */
 
/**
 * DocBlock
 */
 
include("class_game_thumbnail.php");
include("database.php");

$queryString="SELECT id FROM gametable";

$db = new database; //create database object

$result=$db->quickQuery($queryString); //connect to DB; run query; disconnect; return result


if(is_bool($result) ==false) //check if query has a result set
{
	$row = mysqli_fetch_row($result);
	$j = 0; //index for array
	
	do{	
		$idArray[$j]= $row[0];
		$row = mysqli_fetch_row($result);
		$j++;
	} while ($row);
}

//get the length
$arrSize = sizeof($idArray);
$length = $arrSize;

if($arrSize>=2)
	$length = 2; //set length to 10 for the loop
	

//echo "<h4>size: {$arrSize}</h4>";
//echo "<h4>length: {$length}</h4>";
	
/*
echo "Arry: <br />";

for($i=0; $i<$length; ++$i)
{
	echo $idArray[$i]."<br />";	
	
}
*/

//shuffle : random
shuffle($idArray);

//Pick the top x number of element
for($x=0; $x<$length; ++$x)
{
	$gameQuery = "SELECT thumbnail, title, description, origin, semester, platform, createdDate
					FROM gametable
					WHERE id='". $idArray[$x] ."'";
					
	$gameResult = $db->quickQuery($gameQuery); //query
	$arrGames=fetchResultArray($gameResult); //function call
			
			
	$teacherQuery= "SELECT teacherName
					FROM gameteachers
					WHERE gid='". $idArray[$x] ."'";
		
	$teachersResult = $db->quickQuery($teacherQuery); //query			
	$teacherValues=fetchResult($teachersResult); //function call
				
				
	$authorsQuery= "SELECT authorName
					FROM gameauthors
					WHERE gid='". $idArray[$x] ."'";
		
	$authorsResult = $db->quickQuery($authorsQuery); //query
	$authorVaules=fetchResult($authorsResult); //function call
				
				
	$genresQuery= "SELECT genre
					FROM gamegenres
					WHERE gid='". $idArray[$x] ."'";
		
	$genresResult = $db->quickQuery($genresQuery); //query
	$genreValues=fetchResult($genresResult); //function call
		
		
	//send all the information to GameThumnail class
	?>
        
    <div onclick= "<?php echo "ajaxLoadContentByID('contentinfo', 'src/game.php', ", "'id=". $idArray[$x] ."'", ", false);" ?>" >
	<?php $thumbnail = new GameThumbnail(
									   $arrGames["thumbnail"], $arrGames["title"], $arrGames["description"], 
									   $genreValues, $authorVaules, $arrGames["semester"], $arrGames["origin"], 
									   $arrGames["platform"], $arrGames["createdDate"]);
	$thumbnail->printSelf(); ?>
    </div> <br />
<?php
	
	
}



/**
 * Function that loop through all the possible values from the result 
 * and store them into one variable
 *
 * Return string
 */
function fetchResult($typeResult){
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

?>
