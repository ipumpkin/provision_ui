<?php

namespace Drupal\provision_ui;

use Aegir\Provision\Console\ArgvInput;
use Aegir\Provision\Console\ConsoleOutput;
use Aegir\Provision\Console\ProvisionStyle;
use Symfony\Component\Console\Output\BufferedOutput;
use Drupal\Core\Entity\EntityInterface;
use Aegir\Provision\Context;
use Drupal\provision_ui\Provision\Config;
use Aegir\Provision\Provision;
use Drupal\provision_ui\Provision\ArrayOutput;
use Drupal\provision_ui\Entity\TaskEckEntity;
use Drupal\Component\Serialization\Json;
use Drupal\provision_ui\Plugin\QueueWorker\ProvisionTaskQueue;

class ProvisionUI {
  
  /**
   * 
   * @param EntityInterface $context
   * @param string $new
   * @return string[]|number[]
   */
  public static function taskSave(EntityInterface $context = null, $new = false){
    $class = Context::getClassName($context->getEntityTypeId());
    $method = "option_documentation";
    $options = [];
    foreach ($class::{$method}() as $key => $property) {
      if(!empty($context->{$key}->value)){
        $options[$key] = $context->{$key}->value;
      }
    }
    $options['context_type'] = $context->getEntityTypeId();
    $options['context_class'] = $class;
    
    $task = TaskEckEntity::create([
      'title' => "Save : " . $context->label(),
      'type' => "{$context->getEntityTypeId()}_task",
      'task_type' => 'save',
      'task_status' => TaskEckEntity::TASK_STATUS_QUEUE,
      'arguments' => Json::encode([]),
      'options' => Json::encode($options),
      'task_queue' => ProvisionTaskQueue::QUEUE_ID,
      'task_extra' => Json::encode([]),
      $context->getEntityTypeId() => $context
    ]);
    $task->save();
    
    return $task;
  }
  /**
   * 
   * @param EntityInterface $context
   * @param string $sub_command "add"(default), "remove", or "configure"
   * @return string[]|number[]
   */
  public static function taskServices(EntityInterface $context, $sub_command = 'add'){
    if(!empty($context->services)){
      foreach ($context->services as $item){
        $service = $item->entity;
        $args = [];
        $options = [];
        
        $args[] = $sub_command;
        $args[] = $service->getService();
        $args[] = $context->machine_name->value;
        $options['service_type'] = $service->getType();
        
        $properties = $service->getProperties();
        if($properties){
          $options += $properties;
        }
        $task = TaskEckEntity::create([
          'title' => "Service : " . $context->label(),
          'type' => "{$context->getEntityTypeId()}_task",
          'task_type' => 'services',
          'task_status' => TaskEckEntity::TASK_STATUS_QUEUE,
          'arguments' => Json::encode($args),
          'options' => Json::encode($options),
          'task_queue' => ProvisionTaskQueue::QUEUE_ID,
          'task_extra' => Json::encode([]),
          $context->getEntityTypeId() => $context
          ]);
        $task->save();
      }
    }
    if(!empty($context->service_subscriptions)){
      foreach ($context->service_subscriptions as $item){
        $service = $item->entity;
        $args = [];
        $options = [];
        
        $args[] = $sub_command;
        $args[] = $service->getService();
        $args[] = 'server_' . $service->getServer();
        
        $properties = $service->getProperties();
        if($properties){
          $options += $properties;
        }
        
        $task = TaskEckEntity::create([
          'title' => "Service : " . $context->label(),
          'type' => "{$context->getEntityTypeId()}_task",
          'task_type' => 'services',
          'task_status' => TaskEckEntity::TASK_STATUS_QUEUE,
          'arguments' => Json::encode($args),
          'options' => Json::encode($options),
          'task_queue' => ProvisionTaskQueue::QUEUE_ID,
          'task_extra' => Json::encode([]),
          $context->getEntityTypeId() => $context
          ]);
        $task->save();
      }
    }
  }
  /**
   * 
   * @param EntityInterface $context
   * @return \Drupal\provision_ui\string[]|\Drupal\provision_ui\number[]
   */
  public static function taskVerify(EntityInterface $context){
    
    $task = TaskEckEntity::create([
      'title' => "Verify : " . $context->label(),
      'type' => "{$context->getEntityTypeId()}_task",
      'task_type' => 'verify',
      'task_status' => TaskEckEntity::TASK_STATUS_QUEUE,
      'arguments' => Json::encode([]),
      'options' => Json::encode([]),
      'task_queue' => ProvisionTaskQueue::QUEUE_ID,
      'task_extra' => Json::encode([]),
      $context->getEntityTypeId() => $context
      ]);
    $task->save();
    
    return $task;
  }
  /**
   * 
   * @param string $command
   * @param EntityInterface $context
   * @param array $options
   * @param array $args
   * @return string[]|number[]
   */
  public static function provisionCommand($command, EntityInterface $context = null, $options = [], $args = []){
    
    $logger = \Drupal::logger('Provision UI');
    
    $argv = [Provision::APPLICATION_NAME, $command];
    $argv = array_merge($argv, $args);
    if($context){
      $argv[] = "--context={$context->getEntityTypeId()}_{$context->machine_name->value}";
    }
    foreach ($options as $key => $value){
      $argv[] = "--{$key}={$value}";
    }
    $argv[] = "-n";
    // Create input output objects.
    $input = new ArgvInput($argv);
//     $output = new ConsoleOutput();
    $output = new ArrayOutput();
    $io = new ProvisionStyle($input, $output);
    
    $config = new \Aegir\Provision\Console\Config($io,TRUE);
//     $config = new Config($io,false);

    // Create the app.
    $provision = new \Drupal\provision_ui\Provision\Provision($config, $input, $output);
    
    // Run the app.
    $status_code = $provision->run($input, $output);
    
    $result = [
      'status_code' => $status_code,
      'output' => $output->fetch()
    ];
    
    $command = $input->getFirstArgument();
    $logger->debug("Contexts:" . print_r(array_keys($provision->getAllContexts()),1));
    $logger->debug("Command:" . print_r($command,1));
    $logger->debug("Argv:" . print_r($argv,1));
    $logger->debug("Result:" . print_r($result,1));
    return $result;
  }
  
  public static function serverList($service_type){
    $storage = \Drupal::entityTypeManager()->getStorage('server');
    $query = $storage->getQuery();
    $query->condition('services','%_' . $service_type, 'LIKE');
    $result = $query->execute();
    $options = [];
    foreach ($result as $id){
      $entity = $storage->load($id);
      $options[$entity->machine_name->value] = $entity;
    }
    return $options;
  }
}