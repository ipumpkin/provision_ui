<?php
/**
 * @file
 */
namespace Drupal\provision_ui\Plugin\QueueWorker;
use Drupal\Core\Queue\QueueWorkerBase;
use Drupal\Core\Entity\EntityTypeManager;

/**
 * Processes Tasks for deploy.
 *
 * @QueueWorker(
 *   id = "provision_task_queue",
 *   title = @Translation("Provision: Provision task queue"),
 * )
 */
class ProvisionTaskQueue extends QueueWorkerBase{

  const QUEUE_ID = "provision_task_queue";
  /**
   * @var EntityTypeManager
   */
  protected $entity_type_manager;
  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->entity_type_manager = \Drupal::service('entity_type.manager');
  }

  /**
   * {@inheritdoc}
   */
  public function processItem($data) {
    $task = $this->entity_type_manager->getStorage('task')->load($data->task_id);
    if($task){
      $task->execute();
    }
  }
}
