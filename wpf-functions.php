<?


function ip_to_string()
{
	return preg_replace("/[^0-9]/", "_", $_SERVER["REMOTE_ADDR"]);
}

//End Spam time interval check

function mf_u_key()
{
	$pref = "";
	for ($i = 0; $i < 5; $i++)
	{
		$d = rand(0, 1);
		$pref .= $d ? chr(rand(97, 122)) : chr(rand(48, 57));
	}
	return $pref . "-";
}

function MFAttachImage($temp, $name)
{
	//GET USERS UPLOAD PATH
	$upload_dir = wp_upload_dir();
	$path = $upload_dir['path'] . "/";
	$url = $upload_dir['url'] . "/";
	$u = mf_u_key();
	$name = sanitize_file_name($name);
	if (!empty($name))
		move_uploaded_file($temp, $path . $u . $name);
	return "\n[img]" . $url . $u . $name . "[/img]";
}

function MFGetExt($str)
{
	//GETS THE FILE EXTENSION BELONGING TO THE UPLOADED FILE
	$i = strrpos($str, ".");
	if (!$i)
	{
		return "";
	}
	$l = strlen($str) - $i;
	$ext = substr($str, $i + 1, $l);
	return $ext;
}

function mf_check_uploaded_images()
{
	$valid = array('im1' => true, 'im2' => true, 'im3' => true);
	if (!empty($_FILES))
	{
		if ($_FILES["mfimage1"]["error"] > 0 && !empty($_FILES["mfimage1"]["name"]))
			$valid['im1'] = false;
		if ($_FILES["mfimage2"]["error"] > 0 && !empty($_FILES["mfimage2"]["name"]))
			$valid['im2'] = false;
		if ($_FILES["mfimage3"]["error"] > 0 && !empty($_FILES["mfimage3"]["name"]))
			$valid['im3'] = false;
	}
	if (!empty($_FILES["mfimage1"]["name"]))
	{
		$ext = strtolower(MFGetExt(stripslashes($_FILES["mfimage1"]["name"])));
		if ($ext != "jpg" && $ext != "jpeg" && $ext != "bmp" && $ext != "png" && $ext != "gif")
			$valid['im1'] = false;
	}
	else
		$valid['im1'] = false;
	if (!empty($_FILES["mfimage2"]["name"]))
	{
		$ext = strtolower(MFGetExt(stripslashes($_FILES["mfimage2"]["name"])));
		if ($ext != "jpg" && $ext != "jpeg" && $ext != "bmp" && $ext != "png" && $ext != "gif")
			$valid['im2'] = false;
	}
	else
		$valid['im2'] = false;
	if (!empty($_FILES["mfimage3"]["name"]))
	{
		$ext = strtolower(MFGetExt(stripslashes($_FILES["mfimage3"]["name"])));
		if ($ext != "jpg" && $ext != "jpeg" && $ext != "bmp" && $ext != "png" && $ext != "gif")
			$valid['im2'] = false;
	}
	else
		$valid['im3'] = false;
	return $valid;
}


?>