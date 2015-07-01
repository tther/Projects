<?php
/**
 * File: 		database.php
 * Purpose:		Object class that contains common sql functions that allow access to the database
 * Author:		Toua Vang
 * Data:		2/21/2012
 */


class database {
	
	private $DBConn = ""; //private variable
		
	/** 
	 * Main function for query
	 * Return result set or nothing if use insert
	 */
	public function quickQuery($sqlString){
		$this->connect(); //connect to the DB		
		$sqlResult=$this->runQuery($sqlString);	//run the query	
		$this->disconnect(); //close connection to DB
		return $sqlResult;
	}

	/**
	 * Connect to the database server and then to the database
	 */
	public function connect(){
		$this->DBConn = @mysqli_connect("mscs-mysql.uwstout.edu", "gddUser", "gdd451!")
			or die("<p>Unable to connect to the database server.</p>"
			. "<p style='color: red'>Error code " . mysqli_connect_errno()
			. ": " . mysqli_connect_error()). "</p>";
		$this->codingMsg("Successfully connected to the database server.");	//coding msg
		
		@mysqli_select_db($this->DBConn, "gddGames")
			or die("<p>Unable to select the database.</p>"
			. "<p style='color: red'>Error code ".mysqli_errno($this->DBConn)
			. ": ".mysqli_error($this->DBConn)) . "</p>";
			
		$this->codingMsg("Successfully opened the database.");	//coding msg
	}
		
	/**
	 * Function contain mysqli_query; run the query
	 */
	public function runQuery($qString){
		
		$queryResult=@mysqli_query($this->DBConn, $qString)
			or die("<p>Unable to execute the query.</p>"
			. "<p style='color: red'>Error code ".mysqli_errno($this->DBConn)
			. ": " .mysqli_error($this->DBConn)) . "<p>";
		$this->codingMsg("Successfully executed the query");	
		
		return $queryResult;
		
	}
	
	/** 
	 * Close the connection to the DB server
	 */
	public function disconnect(){
		mysqli_close($this->DBConn);
		$this->codingMsg("Close connection to DB");	//coding msg
	}	

	
	/**
	 * Function that closes result set that is no longer being use
	 */
	public function resultClose($result){
		mysqli_free_result($result);
		$this->codingMsg("Successfully close result.");
	}
	
	/** 
	 * Function that take in an string, then escapes special characters for preventing SQL injection
	 */
	public function checkString($input){
		$safeInput = addslashes($input); 
		return $safeInput;
	}
	
	/**
	 * Function to display the coding message, so coder can fix error easier
	 */
	public function codingMsg($msg){
		$showMsg=false;
		if($showMsg)
			echo "<p style='color: #0F0'>".$msg."</p>";
	}
	
}

?>
