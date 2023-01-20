<!DOCTYPE html>
<html>
 <head>
  <title>Find string in file PHP</title>
 </head>
 <body>
<!--
	Usefull link to speed up search code Please visit if you have time
	http://stackoverflow.com/questions/15041608/searching-all-files-in-folder-for-strings
-->

 <?php

function listFolderFiles($string, $dir, $extArray){
		$ffs = scandir($dir);
		foreach($ffs as $ff){
			if($ff != '.' && $ff != '..'){
				if(is_dir($dir.'/'.$ff)){
					listFolderFiles($string, $dir.'/'.$ff, $extArray);
				}else{
					$extension = pathinfo($dir.'/'.$ff, PATHINFO_EXTENSION);
					if(!empty($extArray)){
						if(in_array($extension,$extArray)){
							$content = file_get_contents($dir.'/'.$ff);
							if (strpos(strtolower($content), strtolower($string)) !== false) {
								echo $dir.'/'.$ff."<br>";
							}
						}
					}
					else{
							$content = file_get_contents($dir.'/'.$ff);
							if (strpos(strtolower($content), strtolower($string)) !== false) {
								echo $dir.'/'.$ff."<br>";
							}
						}
				}
			}
		}
	}
if(isset($_POST['string'])){



	$string = $_POST['string'];
	if($_POST['dir']){
		$dir = $_POST['dir'];

	}else{
		$dir = getcwd();
	}
	$extArray = array();
	if($_POST['ext'] != ""){ $extArray = explode(",",$_POST['ext']);}

	echo 'Search string: <b>'.$_POST['string'].'</b></br>';
	echo 'Search directory: <b>'.$_POST['dir'].'</b><br>';

	listFolderFiles($string, $dir, $extArray);

  }

?>
  <div id="content" style="margin-top:10px;height:100%;">
   <center><h1>Find string in file PHP</h1></center>
   <form action="" method="POST">
    String : <input name="string" size="35" placeholder="Enter string" required = "required" />

	 Directory Name : <input name="dir" size="35" placeholder="Enter directory name"/>
	 <input type="hidden" name="ext" size="35" placeholder="like .xml,.php,.phtml"/>

    <input type="submit" name="submit" value="Search"/>
   </form>

  </div>


 </body>
</html>
