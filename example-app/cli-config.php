<?php

require '../vendor/autoload.php';

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Migrations\Configuration\EntityManager\ExistingEntityManager;
use Doctrine\Migrations\DependencyFactory;
use Doctrine\Migrations\Configuration\Migration\PhpFile;
use Primavera\Framework\Application;

$app = new Application();

$config = new PhpFile('migrations.php');

return DependencyFactory::fromEntityManager($config, new ExistingEntityManager($app->getContainer()->get(EntityManagerInterface::class)));