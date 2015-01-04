<?php
// turn authentication on or off
// For php running under CGI you may need to this .htaccess as well:
// http://www.besthostratings.com/articles/http-auth-php-cgi.html
$isRestricted = TRUE;

// if isRestricted is TRUE, then use the following format for adding mutiple users 
// user => password
$users = array("admin" => "admin", "user" => "user");

// Image formats to allow
$valid_extentions_img = array("jpg","jpeg","png","gif","bmp");

// Video formats to allow 
$valid_extentions_mov = array("avi","mov","flv","vob","");

// defaulet cache directory
$cache_dir = "cache/";

// Default width for thumbnails
$imageWidth = 100;

// Default height for thumbnails
$imageHeight = 100;

// Realm label
$realm = 'Restricted area';

// function to parse the http auth header
function http_digest_parse($txt)
{
    // protect against missing data
    $needed_parts = array('nonce'=>1, 'nc'=>1, 'cnonce'=>1, 'qop'=>1, 'username'=>1, 'uri'=>1, 'response'=>1);
    $data = array();
    $keys = implode('|', array_keys($needed_parts));
    preg_match_all('@(' . $keys . ')=(?:([\'"])([^\2]+?)\2|([^\s,]+))@', $txt, $matches, PREG_SET_ORDER);
    foreach ($matches as $m) {
        $data[$m[1]] = $m[3] ? $m[3] : $m[4];
        unset($needed_parts[$m[1]]);
    }
    return $needed_parts ? false : $data;
}

if($isRestricted)
{
	// Check if some credentials were even sent
	if (empty($_SERVER['PHP_AUTH_DIGEST'])) {
	    header('HTTP/1.1 401 Unauthorized');
	    header('WWW-Authenticate: Digest realm="'.$realm.
	           '",qop="auth",nonce="'.uniqid().'",opaque="'.md5($realm).'"');
	    die('You did not provide a user name and password!');
	}

	// Analyze the PHP_AUTH_DIGEST variable and validity of a username
	if (!($data = http_digest_parse($_SERVER['PHP_AUTH_DIGEST'])) ||
	    !isset($users[$data['username']]))
	    die('Wrong Credentials!');

	// Generate the valid response
	$A1 = md5($data['username'] . ':' . $realm . ':' . $users[$data['username']]);
	$A2 = md5($_SERVER['REQUEST_METHOD'].':'.$data['uri']);
	$valid_response = md5($A1.':'.$data['nonce'].':'.$data['nc'].':'.$data['cnonce'].':'.$data['qop'].':'.$A2);

	// Check validity of local and submited hash
	if ($data['response'] != $valid_response)
	    die('Wrong Credentials!');	
}


// Read all file in the directory
$files = scandir('.');

// Create the title 
$path = $_SERVER['SCRIPT_FILENAME'];
$path = explode("/",$path);
$title = ucfirst($path[count($path)-2])." Photos";

// Check if cache directory is already created
if(!is_dir($cache_dir))
{
	//  Create cache directory
	mkdir('cache', 0755);
}
?><!doctype html>
<html itemscope="" itemtype="http://schema.org/WebPage" lang="en">
<head>
	<style>
	* {
	  margin: 0;
	  padding: 0;
	}

	body {
	  background: #333;
	}

	div {
	  width: 900px;
	  margin: 0 auto;
	  overflow: auto;
	}

	ul {
	  list-style-type: none;
	}

	li img {
	  float: left;
	  margin: 10px;
	  border: 5px solid #fff;

	  -webkit-transition: box-shadow 0.5s ease;
	  -moz-transition: box-shadow 0.5s ease;
	  -o-transition: box-shadow 0.5s ease;
	  -ms-transition: box-shadow 0.5s ease;
	  transition: box-shadow 0.5s ease;
	}

	li img:hover {
	  -webkit-box-shadow: 0px 0px 7px rgba(255,255,255,0.9);
	  box-shadow: 0px 0px 7px rgba(255,255,255,0.9);
	}
	
	
	</style>
</head>
<body>
	<h1 style="text-align:center;color:#FFF;"><?php echo $title;?></h1>
	<div>
		<ul>
			<?php
			
			foreach ($files as $file)
			{
				// Seperate out file and extention
				list($file_name, $extension) = explode( '.', $file );
				$extension = strtolower($extension)."";
				
				// Skip if file name is empty, probably a hidden file
				if($file_name == "")
				{
					continue;
				}
				
				// Create a HTML list item
				echo '<li>';
				
				// Check if its a valid extention for image
				if(in_array($extension, $valid_extentions_img))
				{
					// Check there is no thumbnail for this image
					if(!is_file($cache_dir.$file))
					{
						// Use imagemagick to generate a thumbnail
						`convert $file -resize $imageWidthx$imageHeight^ $cache_dir$file`;
					}
					
					// Create a link and refrence for the image
					echo "<a href=\"$file\" download><img src=\"$cache_dir$file\" width=\"$imageWidth\" / ></a>";
				}
				// Check if its a valid extention for video
				elseif(in_array($extension, $valid_extentions_mov))
				{
					// Check there is no thumbnail for this video
					if(!is_file($cache_dir.$file_name.".jpg"))
					{
						// Use FFmpeg to generate a thumbnail of the first frame
						`ffmpeg -ss 00:00:01 -i $file -vframes 1 $cache_dir$file_name.jpg`;
					}
					
					// Create a link and refrence for the image
					echo "<a href=\"$file\" download><img src=\"$cache_dir$file_name.jpg\" width=\"$imageWidth\" /></a>";
				}
				
				// Flust the buffer to start displaying something
				flush();
				
				// Closing tag for the list item
				echo '</li>';
			}
			?></ul>
		</div>
</body>
</html>