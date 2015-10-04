<?php
/*
Gibbon, Flexible & Open School System
Copyright (C) 2010, Ross Parker

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program. If not, see <http://www.gnu.org/licenses/>.
*/

include "../../functions.php" ;
include "../../config.php" ;

//New PDO DB connection
try {
  	$connection2=new PDO("mysql:host=$databaseServer;dbname=$databaseName;charset=utf8", $databaseUsername, $databasePassword);
	$connection2->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	$connection2->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
}
catch(PDOException $e) {
  echo $e->getMessage();
}

@session_start() ;

//Set timezone from session variable
date_default_timezone_set($_SESSION[$guid]["timezone"]);

$gibbonSchoolYearID=$_POST["gibbonSchoolYearID"] ;

$URL=$_SESSION[$guid]["absoluteURL"] . "/index.php?q=/modules/" . getModuleName($_POST["address"]) . "/awards_grant_add.php&gibbonPersonID2=" . $_GET["gibbonPersonID2"] . "&awardsAwardID2=" . $_GET["awardsAwardID2"] . "&gibbonSchoolYearID=$gibbonSchoolYearID" ;

if (isActionAccessible($guid, $connection2, "/modules/Awards/awards_grant_add.php")==FALSE) {
	//Fail 0
	$URL.="&addReturn=fail0" ;
	header("Location: {$URL}");
}
else {
	//Proceed!
	if (isset($_POST["gibbonPersonIDMulti"])) {
		$gibbonPersonIDMulti=$_POST["gibbonPersonIDMulti"] ; 
	}
	else {
		$gibbonPersonIDMulti=NULL ; 
	}
	$awardsAwardID=$_POST["awardsAwardID"] ; 
	$date=$_POST["date"] ; 
	$comment=$_POST["comment"] ; 
	
	if ($gibbonPersonIDMulti==NULL OR $date=="" OR $awardsAwardID=="" OR $gibbonSchoolYearID=="") {
		//Fail 3
		$URL.="&addReturn=fail3" ;
		header("Location: {$URL}");
	}
	else {
		$partialFail=FALSE ;
		
		foreach ($gibbonPersonIDMulti AS $gibbonPersonID) {
			//Write to database
			try {
				$data=array("awardsAwardID"=>$awardsAwardID, "gibbonSchoolYearID"=>$gibbonSchoolYearID, "date"=>dateConvert($guid, $date), "gibbonPersonID"=>$gibbonPersonID, "comment"=>$comment, "gibbonPersonIDCreator"=>$_SESSION[$guid]["gibbonPersonID"]); 
				$sql="INSERT INTO awardsAwardStudent SET awardsAwardID=:awardsAwardID, gibbonSchoolYearID=:gibbonSchoolYearID, date=:date, gibbonPersonID=:gibbonPersonID, comment=:comment, gibbonPersonIDCreator=:gibbonPersonIDCreator" ;
				$result=$connection2->prepare($sql);
				$result->execute($data);
			}
			catch(PDOException $e) { 
				$partialFail=TRUE ;
			}
			
			$awardsAwardStudentID=$connection2->lastInsertID() ;
		
			//Attempt to add like
			$likeComment="" ;
			if ($comment!="") {
				$likeComment.=$comment ;
			}
			$return=setLike($connection2, "Awards", $_SESSION[$guid]["gibbonSchoolYearID"], "awardsAwardStudentID", $awardsAwardStudentID, $_SESSION[$guid]["gibbonPersonID"], $gibbonPersonID, "Award Granted", $likeComment) ;
		}
		
		if ($partialFail==TRUE) {
			//Fail 5
			$URL.="&addReturn=fail5" ;
			header("Location: {$URL}");
		}
		else {
			//Success 0
			$URL.="&addReturn=success0" ;
			header("Location: {$URL}");
		}
	}
}
?>