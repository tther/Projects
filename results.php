<?php
session_save_path("d:\web\gdd\dev\sessions");
ini_set('session.gc_probability', 1);
//ini_set('session.gc_maxlifetime', 60);
session_start();

/**
 * File: 		results.php
 * Purpose:		Search the database for match and generate the result
 * Author:		Toua Vang
 * Date:		3/7/2012
 * Update:		4/9/2012, 4/16/2012
 */
 
/**
 * Important variables:
 * $currentChar 	- store current page's letter
 * $lblAlphabets 	- store the set of browsing alphabet letters
 * $pageNum 		- store the page number
 * $lblPageNum		- store the set of browsing page numbers
 * $postParam
 
 * Session variables:
 * SESSION['searchResult']
 * -store POST parameters
 */

include("class_game_thumbnail.php");
include("database.php");
include("function_getGameInfo.php");


//var_dump($_POST);


//create an array that contains the index name of all the parameters pass in and define the array's size
$arrParam = array('input', 'title', 'platform', 'inception', 'semester', 'creators', 'teachers', 'keywords', 'genre');
$paramSize = sizeof($arrParam);

//declare variables
$input="NOTHING"; //to store the input
$noResult="No Result <br />"; //to store the no result message; show at the end
$strParams = ""; //create the POST parameters for reload the old result; mainly for the back button
$browseAll=false; //Flag variable to determine if the query string should be override to get all the games from the database

if(isset($_POST['browse'])) //POST data coming from index page
	$browseAll=$_POST['browse'];

//create database object
$dbObj=new database;

for($c=0; $c<$paramSize; $c++) //loop and generate the POST string
{
	if(isset($_POST[$arrParam[$c]]))
	{
	  if(is_array($_POST[$arrParam[$c]])){
		  $size=sizeof($_POST[$arrParam[$c]]);
		  for($b=0; $b<$size;$b++)
			  $strParams .= $arrParam[$c]."[]=".$_POST[$arrParam[$c]][$b]."&";
	  }
	  else{
		  $strParams .= $arrParam[$c]."=".$_POST[$arrParam[$c]]."&";
	  }
	}
}
$postParam=substr($strParams, 0, strlen($strParams)-1); //cut off the last '&'

if($browseAll)
	$postParam="browse=true";

//we will store the postParam to the session later at the end of this file


//check if the input is set and sanitize
if(isset($_POST[$arrParam[0]])){
	$input=$_POST[$arrParam[0]];
	$input= $dbObj->checkString($input);
}

/********************************************************************************/
/*  				QUERY (STRING) BUILDING SECTION #1  						*/
/*					   		alphabet links										*/
/********************************************************************************/

$queryTitle = ", gametable.title"; //add to master query for getting the title

//define the first half of the query string
$querySelect = "SELECT DISTINCT gametable.id".$queryTitle."
				FROM gametable

				LEFT JOIN gamekeywords ON gametable.id = gamekeywords.gid
				LEFT JOIN gamegenres ON gametable.id = gamegenres.gid
				LEFT JOIN gameauthors ON gametable.id = gameauthors.gid
				LEFT JOIN gameteachers ON gametable.id = gameteachers.gid

				WHERE ";

//create an array that conatins all the subset query string
$arrQString = array("gametable.title LIKE '%".$input."%'", "gametable.platform LIKE '%".$input."%'", "gametable.origin LIKE '%".$input."%'", 
					"gametable.semester LIKE '%".$input."%'", "gameauthors.authorName LIKE '%".$input."%'", "gameteachers.teacherName LIKE '%".$input."%'", 
					"gamekeywords.keyword LIKE '%".$input."%'");
				
$queryBuffer=""; //define a buffer to assemble the other part
$strOR=""; //define the necessary string OR

/** 
 * Use loop to get the parameters: platform, inception, semester, creators, teachers, keywords. 
 * Then add the parameter's assocated sub query to the buffer. 
 */
for($i=1, $n = $paramSize -1; $i<$n; ++$i){
		if(isset($_POST[$arrParam[$i]]))
		{
			$parValue=$_POST[$arrParam[$i]];
			
			if($parValue=="true"){
				$queryBuffer=$queryBuffer." ". $strOR . $arrQString[$i-1]; //add sub query string to buffer
				$strOR = "OR "; //add OR for the next sub query
			}
		}
}

//check if genre is set
if(isset($_POST[$arrParam[8]])){
	$arrGenre=$_POST[$arrParam[8]];	
	$length = sizeof($arrGenre);
	
	/** loop the genre array and build up the query string */
	for($j=0; $j<$length; ++$j)
	{
		$safeGenre = $dbObj->checkString($arrGenre[$j]);
		$queryBuffer = $queryBuffer." ". $strOR. "gamegenres.genre LIKE '%".$safeGenre."%'";	
	}
}

//store the query for showing only results' title start with certain letter
$queryAlphabet = "";

//assemble all the sub query strings together for the FIRST part
//to get all the results; get all the title
$qString=$querySelect.$queryBuffer."ORDER BY gametable.title";

if($browseAll)
	$qString= "SELECT DISTINCT gametable.id".$queryTitle." FROM gametable ORDER BY gametable.title";


//create an array of letters to generate the alphabet for browsing
$arrAlph=array("#","A","B","C","D","E","F","G","H","I","J","K","L","M","N","O","P","Q","R","S","T","U","V","W","X","Y","Z");
$alphBuffer=""; //create a buffer to store all the letters that exist in the result


$resultLetter=$dbObj->quickQuery($qString);  //call database and execute query

if(is_bool($resultLetter) ==false) //check if query has a result set
{
	$rowLetter = mysqli_fetch_row($resultLetter); //fetch the result
	
	
	
	/** loop through every result and store the first character of the title and count the game  */
	do{
		$firstChar = $rowLetter[1]{0}; //store the first character of the title
		
		//check if the character is not a letter
		if(preg_match("/[^a-zA-Z]/",$firstChar))
			$firstChar = "#"; //make all non-letter #
			
		//now check if the character is already exist in the buffer; no repeat letter
		if(!(stristr($alphBuffer,$firstChar)))
			$alphBuffer.= $firstChar; //add letter to buffer
		//echo "{$rowLetter[1]} <br />";	//*********************************************************************************************
		$rowLetter = mysqli_fetch_row($resultLetter);
	} while ($rowLetter);
}

$lgArrAlph = sizeof($arrAlph); //get length of the buffer


//*****  Define the previous character pass in *****//
$currentChar = "All"; //store the current letter of the page
if(isset($_POST['letter']))
{
	$currentChar = $_POST['letter'];
	if($currentChar!="All")
	{
		$queryAlphabet = " AND gametable.title LIKE '".$currentChar."%' "; //this sub-query will determine the amount of page number display
		if($browseAll)
			$queryAlphabet = " WHERE gametable.title LIKE '".$currentChar."%' "; //for our getting all games query string
	}
}

//*****  Create the browsing letters  *****//
$lblAlphabets = "<div id='alphabet'>";

if($currentChar=="All") //check if our characters are every letters of the alphabet
{
	$lblAlphabets.= "[All]"; //add the string to our string
}
else //it not all alphabet so make the letter a link (clickable)
{
	$thisChar="All";
	$lblAlphabets.= "<a onclick= \"ajaxLoadContentByID('contentinfo', 'src/results.php', '".$postParam."&letter=".$thisChar."' , true);\"><b>All</b></a>";	
}
$lblAlphabets.= "&nbsp; &nbsp;";

for($i=0; $i<$lgArrAlph;$i++)
{
	if($currentChar==$arrAlph[$i])
	{
		$lblAlphabets.="[{$arrAlph[$i]}]";
	}
	elseif(stristr($alphBuffer,$arrAlph[$i]))
	{
		$lblAlphabets.="<a onclick=\"ajaxLoadContentByID('contentinfo', 'src/results.php', '".$postParam."&letter=".$arrAlph[$i]."' , true);\"><b> {$arrAlph[$i]} </b></a>";
	}
	else
	{
		$lblAlphabets.= $arrAlph[$i];
	}
	$lblAlphabets.= "&nbsp; &nbsp;";
	
}

$lblAlphabets.="</div>";

//echo $lblAlphabets;
//echo "<p>".$alphBuffer."</p>";
//echo "<p>".$ctrGames."</p>";
//$n= $ctrGames / 2;
//echo "<p>".ceil(43)."</p>";


/********************************************************************************/
/*  						QUERY SECTION #2  									*/
/*					       page number links									*/
/********************************************************************************/

//query string that will determine the number of games
$qStrPage=$querySelect."(".$queryBuffer.")".$queryAlphabet."ORDER BY gametable.title";

//User want to browse all games instead
if($browseAll)
	$qStrPage="SELECT DISTINCT gametable.id".$queryTitle." FROM gametable ".$queryAlphabet."ORDER BY gametable.title";

$resultPage=$dbObj->quickQuery($qStrPage);
$numOfGames=0;

if(is_bool($resultPage) ==false) //if there is a result set
{
	$numOfGames=mysqli_num_rows($resultPage); //get the number of games
}

$pageNum=1; //default page is 1 when first get here
$xResults = 5; //amount of results want to show per page
$numOfPages = $numOfGames / $xResults;
$numOfPages = ceil($numOfPages); //round up if more than its integer

//check if there any previous current page number pass in
if(isset($_POST['page']))
	$pageNum = $_POST['page'];

//*****  Create the browsing page numbers  *****//
$lblPageNum="<div id='pageNum'> Page: ";

//loop and generate the page numbe
for($i=1; $i<=$numOfPages;$i++)
{
	if($pageNum==$i) //if it's current page; not clickable
	{
		$lblPageNum.=" [$i] ";
	}
	else
	{
		$lblPageNum.="<a onclick=\"ajaxLoadContentByID('contentinfo', 'src/results.php', '".$postParam."&letter=".$currentChar."&page=".$i."' , true);\"> $i </a>";
	}
	$lblPageNum.="&nbsp;";
}
$lblPageNum.="</div>";

echo "<br />";
echo $lblAlphabets;
echo "<br />";



/********************************************************************************/
/*  						QUERY SECTION #3  									*/
/*					       Content: Games Info									*/
/********************************************************************************/

//Use page Number (var pageNum) above to show the right result

$initGame=($pageNum - 1)*($xResults);  // use pageNum and xResults to determine where we are

//Sub-query to limit the results
$queryLimit="
			ORDER BY gametable.title
			LIMIT ".$initGame.", ".$xResults;

//Add the limit string to the master query
$queryString=$querySelect."(".$queryBuffer.")".$queryAlphabet.$queryLimit;

if($browseAll)
	$queryString="SELECT DISTINCT gametable.id".$queryTitle." FROM gametable ".$queryAlphabet.$queryLimit;
	
//echo $queryString; //**********************************************************************

$result=$dbObj->quickQuery($queryString); //call database query again

if(is_bool($result) ==false) //check if query has a result set
{
	$row = mysqli_fetch_row($result); //fetch the id
	
	//echo "<table align='center' width='660' border='0' cellspacing='6px' cellpadding='4px'>"; //-----TABLE TAG open
	//$ctr=0; //counter for tag tr
	
	/** loop through every id and query to get the necessary information from the DB  */	
	do{

		// Call the function from function_getGameInfo.php
		// and store the array for uses in the thumbnail
		$gameInfoArr=getGameInfo($dbObj, $row[0]);
		
		
		if($gameInfoArr["title"]!="")
			$noResult=""; //there's a result set messeage to empty
			
		//if($ctr==0)		//new row if current row has 2 column
			//echo "<tr>";
		
		//send all the information to GameThumnail class if there exist an id
		if($row[0]!="")
		{
			$clean_title = str_replace(" ", "_", trim($gameInfoArr["title"]));
			  $game_dir = "games/_". $row[0] . "_" . $clean_title . "/";
		?>
        
          <?php //<div class="game" onclick= " echo "ajaxLoadContentByID('contentinfo', 'src/game.php', ", "'id=$row[0]'", ", false);" " > ?>
		<div class="game">
            <a href="src/game.php?id=<?php echo $row[0] ?>">
			  <?php $thumbnail = new GameThumbnail(
				 							   $game_dir.$gameInfoArr["thumb"],$gameInfoArr["title"],$gameInfoArr["desc"], 
											   $gameInfoArr["genre"],$gameInfoArr["author"],$gameInfoArr["semester"],
											   $gameInfoArr["origin"],$gameInfoArr["platform"],$gameInfoArr["year"]);
			  $thumbnail->printSelf(); ?>
    	    </a>
		</div><br />
          <?php //</div> ?>
		<?php
		}
		//$ctr++;
		//if($ctr==2){ //close the tag TR when at the end of 2nd cell in the row
			//echo "</tr>";
			//$ctr=0;
		//}
		
		
		$row = mysqli_fetch_row($result);
	} while ($row);
	
	//echo "</table>"; //-----TABLE TAG closing

}

//store the POST string into the session variable for later use
$_SESSION['fromPages']=">Result";
$_SESSION['searchResult']=$postParam."&letter=".$currentChar."&page=".$pageNum;

echo $noResult;
echo $lblPageNum;
echo "<br />";

//echo "<br />POST1: ".$_SESSION['fromPages'];
//echo "<br />Session: ".$_SESSION['searchResult'];
?>

<!-- <p>Result page</p>
<a href="src/gameTest.php">CLICK HERE FOR TESTING</a>
<a onclick= "<?php //echo "ajaxLoadContentByID('contentinfo', 'src/results.php', '", $string."&page=1", "', true);" ?>" >page1 </a> -->

