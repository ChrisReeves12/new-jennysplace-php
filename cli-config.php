<?php
require_once "bootstrap.php";
require_once 'init_autoloader.php';

include 'module/Library/Model/Traits/StandardModelTrait.php';

return \Doctrine\ORM\Tools\Console\ConsoleRunner::createHelperSet($em);