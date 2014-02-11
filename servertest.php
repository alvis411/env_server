<?php
header('Access-Control-Allow-Origin: *');
require_once ("nusoap/lib/nusoap.php");

//App config
define("SERVER","pcnguyen.dyndns.org");
define("USERNAME","admin");
define("PASSWORD","admin");
define("DBNAME","video_english");
define("serverURL","http://pcnguyen.dyndns.org");

//Connect DB
function connectDB(){
	$conn=mysql_connect(SERVER,USERNAME,PASSWORD);
	mysql_query('SET NAME "utf8" ');
	if(!$conn)
	{
		die ("Khong the ket noi dc toi database");
	}
	mysql_select_db(DBNAME,$conn) or die(mysql_error());
}

function closeDB(){
	mysql_close($conn);
}

//server test
$server=new soap_server();
$server->configureWSDL('English Video Service',serverURL);

///////////////////////////////
// Register function to soap server:
// $server->register('<function name>', <input value>, <output value>, serverURL);
///////////////////////////////

//register service for user table
$server->register('set_user', array('username'=>'xsd:string','website'=>'xsd:string','role'=>'xsd:string'),array('outcome'=>'xsd:string'), serverURL);
$server->register('get_user', array('id'=>'xsd:string'),array('username'=>'xsd:string','website'=>'xsd:string','role'=>'xsd:string'), serverURL);
$server->register('edit_user', array('id'=>'xsd:string','username'=>'xsd:string','website'=>'xsd:string'),array('outcome'=>'xsd:integer'), serverURL);
$server->register('delete_user', array('id'=>'xsd:string'),array('outcome'=>'xsd:integer'), serverURL);
$server->register('set_password', array('id'=>'xsd:string', 'username'=>'xsd:string','website'=>'xsd:string'),array('outcome'=>'xsd:integer'), serverURL);

//register service for video table
$server->register('addVideo', array('owner'=>'xsd:string','content'=>'xsd:string','url'=>'xsd:string','name'=>'xsd:string'),array('name'=>'xsd:string','id'=>'xsd:string'), serverURL);
$server->register('getVideo', array('id'=>'xsd:string'),array('owner'=>'xsd:string','content'=>'xsd:string','url'=>'xsd:string','name'=>'xsd:string'), serverURL);
$server->register('getVideoByOwner', array('owner'=>'xsd:string'),array('video_id'=>'xsd:string'), serverURL);
$server->register('getVideoByName', array('name'=>'xsd:string'),array('video_id'=>'xsd:string'), serverURL);
$server->register('getContentVideo', array('id'=>'xsd:string'),array('content'=>'xsd:string'), serverURL);
$server->register('editVideo', array('id'=>'xsd:string','owner'=>'xsd:string','content'=>'xsd:string','url'=>'xsd:string','name'=>'xsd:string'),array('outcome'=>'xsd:string'), serverURL);
$server->register('getNameVideo', array('id'=>'xsd:string'),array('name'=>'xsd:string'), serverURL);

//register service for question table
$server->register('set_question', array('video_id'=>'xsd:string','question'=>'xsd:string','a'=>'xsd:string','b'=>'xsd:string','c'=>'xsd:string','d'=>'xsd:string','correct'=>'xsd:string','time'=>'xsd:string'),array('outcome'=>'xsd:string'), serverURL);
$server->register('get_question', array('id'=>'xsd:string'),array('video_id'=>'xsd:string','question'=>'xsd:string','a'=>'xsd:string','b'=>'xsd:string','c'=>'xsd:string','d'=>'xsd:string','correct'=>'xsd:string','time'=>'xsd:string'), serverURL);
$server->register('edit_question', array('id'=>'xsd:string','video_id'=>'xsd:string','question'=>'xsd:string','a'=>'xsd:string','b'=>'xsd:string','c'=>'xsd:string','d'=>'xsd:string','correct'=>'xsd:string','time'=>'xsd:string'),array('outcome'=>'xsd:string'), serverURL);
$server->register('get_correct', array('id'=>'xsd:string'),array('id'=>'xsd:string','correct'=>'xsd:string'), serverURL);
$server->register('set_question_time', array('id'=>'xsd:string','time'=>'xsd:string'),array('outcome'=>'xsd:string'), serverURL);

//khai bao return cho cac function vua dang ky
// luon co connectDB(); va closeDB(); neu dung database

//////////////
//function: addVideo
//input: owner,content,url,name
//return: true: name and id of video inserted. false: id=0 and name=''(insert fail)
//////////////
	function addVideo($owner,$content,$url,$name){
		connectDB();
		$query="INSERT INTO env_video (owner,content,url,name) VALUES ('$owner','$content','$url','$name')";
		$execute=mysql_query($query);
		if($execute){
			//get id of video inserted
			$countRow=mysql_query("SELECT COUNT(id) FROM env_video");
			$latestRow=mysql_result($countRow,0);
			//get name of video inserted
			$nameQuery=mysql_query("SELECT name FROM env_video WHERE id='".$latestRow."'");
			$name=mysql_result($nameQuery,0);
			$videoObect=array('id'=>$latestRow,'name'=>$name);
			return json_encode($videoObject);
		}
		else{
			$videoObject=array('id'=>'','name'=>'');
			return json_encode($videoObject);
		}
		closeDB();	
	}
//////////////
//function: getVideo
//input: videoID
//return: a json object contain id,owner,content,url,name of video
//NOTICE: if there is no videoID in database,our videoObject is null.
//////////////
	function getVideo($videoID){
		connectDB();
		$query="SELECT * FROM env_video WHERE id='".$videoID."'";
		$execute=mysql_query($query) or die(mysql_error());
		while($data=mysql_fetch_row($execute))
		{
		   $videoObject=array('id'=>$data[0],'owner'=>$data[1],'content'=>$data[2],'url'=>$data[3],'name'=>$data[4]);
		}
		closeDB();
		return json_encode($videoObject);
	}
//////////////
//function: getVideoByOwner
//input: owner
//return: owner of this video
//////////////
	function getVideoByOwner($owner){
		connectDB();
		$query="select id from env_video where owner='".$owner."'";
		$execute=mysql_query($query) or die(mysql_error());
		$id=mysql_result($execute,0);
		return $id;
		closeDB();
	}
//////////////
//function: getVideoByName
//input: name
//return: json object of list of video id that have the same name with input name
//////////////
	function getVideoByName($name){
		connectDB();
		$query="SELECT id FROM env_video WHERE name='".$name."'";
		$execute=mysql_query($query) or die(mysql_error());
		$listID=array();
		while($data=mysql_fetch_row($execute))
		{
			array_push($listID,$data[0]);
		}
		return json_encode($listID);
		closeDB();
	}
//////////////
//function: getContentVideo
//input: videoID
//return: content of this videoID
//////////////
	function getContentVideo($videoID){
		connectDB();
		$query="SELECT content FROM env_video WHERE id='".$videoID."'";
		$execute=mysql_query($query) or die(mysql_error());
		$content=mysql_result($execute,0);
		return $content;
		closeDB();
	}
//////////////
//function: editVideo
//input: videoID,owner,content,url,name
//return: true if update is success,false if update is fail
//////////////
	function editVideo($videoID,$owner,$content,$url,$name){
		connectDB();
		$query="UPDATE env_video SET (owner='".$owner."',content='".$content."',url='".$url."',name='".$name."') WHERE id='".$videoID."'";
		$execute=mysql_query($query);
		if($execute){
			return 'UPDATE SUCCESSFULLY';
		}else{
			return 'UPDATE FAIL';	
		}
		closeDB();
	}
//////////////
//function: getNameVideo
//input: videoID
//return: name of this video
//////////////
	function getNameVideo($videoID){
		connectDB();
		$query="SELECT name FROM env_video WHERE id='".$videoID."'";
		$execute=mysql_query($query) or die(mysql_error());
		$name=mysql_result($execute,0);
		return $name;
		closeDB();
	}
$server->service($HTTP_RAW_POST_DATA);
?>
