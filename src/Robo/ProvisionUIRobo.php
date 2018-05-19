<?php

namespace Drupal\provision_ui\Robo;

use League\Container\Container;
use Robo\Robo;

class ProvisionUIRobo extends Robo {
  
  protected static $container_origin = NULL;
  
  public static function createNewContainer($input = null, $output = null, $app = null, $config = null, $classLoader = null)
  {
    // Do not allow this function to be called more than once.
    if (static::hasContainer()) {
      static::$container_origin =  static::getContainer();
    }
    
    if (!$app) {
      $app = static::createDefaultApplication();
    }
    
    if (!$config) {
      $config = new \Robo\Config\Config();
    }
    
    // Set up our dependency injection container.
    $container = new Container();
    static::configureContainer($container, $app, $config, $input, $output, $classLoader);
    
    // Set the application dispatcher
    $app->setDispatcher($container->get('eventDispatcher'));
    
    return $container;
  }
  
  public static function resetConatiner(){
    if(static::$container_origin){
      static::setContainer(static::$container_origin);
    }
  }
}