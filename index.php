<?php

/**
 * Chargement du bootstrap
 */
$bootstrap = __DIR__.'/lib/magepattern/bootstrap.php';
//print $bootstrap;
if (file_exists($bootstrap)){
    require $bootstrap;
}else{
    throw new Exception('Boostrap is not exist');
}
?>
