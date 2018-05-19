<?php

namespace Drupal\provision_ui\Entity;

use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\eck\Entity\EckEntity;
use Drupal\Core\Field\BaseFieldDefinition;
use Aegir\Provision\Context;
use Drupal\Core\DependencyInjection\ClassResolver;
use Aegir\Provision\Property;
use Drupal\Core\Form\FormState;
use Drupal\Core\Form\FormStateInterface;
use Drupal\provision_ui\Plugin\QueueWorker\ProvisionTaskQueue;
use Drupal\Component\Serialization\Json;
use Drupal\provision_ui\ProvisionUI;

class TaskEckEntity extends EckEntity {

  const TASK_STATUS_QUEUE = "queue";
  const TASK_STATUS_PROCESS = "process";
  const TASK_STATUS_SUCCESS = "success";
  const TASK_STATUS_FAILURE = "failure";
  const TASK_STATUS_CANCLE = "cancle";
  
  /**
   *
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {
    $fields = parent::baseFieldDefinitions($entity_type);
    
    $fields['task_type'] = BaseFieldDefinition::create('string')
    ->setLabel(t('Task Type'))
    ->setDescription(t('Command name of provision'))
    ->setRequired(TRUE)
    ->setSetting('max_length', 255)
    ->setDisplayOptions('form', [
      'type' => 'string_textfield',
    ]);
    $fields['task_status'] = BaseFieldDefinition::create('string')
    ->setLabel(t('Task Status'))
    ->setDescription(t('Status of this task'))
    ->setRequired(false)
    ->setDefaultValue(self::TASK_STATUS_QUEUE)
    ->setSetting('max_length', 255)
    ->setDisplayOptions('form', [
      'type' => 'string_textfield',
    ]);
    $fields['arguments'] = BaseFieldDefinition::create('string')
    ->setLabel(t('Task Arguments'))
    ->setDescription(t('Task executed arguments'))
    ->setRequired(false)
    ->setDefaultValue('{}')
    ->setSetting('max_length', 2000)
    ->setDisplayOptions('form', [
      'type' => 'string_textfield',
    ]);
    $fields['options'] = BaseFieldDefinition::create('string')
    ->setLabel(t('Task Options'))
    ->setDescription(t('Task executed Options'))
    ->setRequired(false)
    ->setDefaultValue('{}')
    ->setSetting('max_length', 2000)
    ->setDisplayOptions('form', [
      'type' => 'string_textfield',
    ]);
    $fields['task_queue'] = BaseFieldDefinition::create('string')
    ->setLabel(t('Task Queue Name'))
    ->setDescription(t('The queue name of this task'))
    ->setRequired(false)
    ->setDefaultValue(ProvisionTaskQueue::QUEUE_ID)
    ->setSetting('max_length', 255)
    ->setDisplayOptions('form', [
      'type' => 'string_textfield',
    ]);
    $fields['task_extra'] = BaseFieldDefinition::create('string')
    ->setLabel(t('Task extra data'))
    ->setDescription(t('Extra data of this task'))
    ->setRequired(false)
    ->setDefaultValue("{}")
    ->setSetting('max_length', 255)
    ->setDisplayOptions('form', [
      'type' => 'string_textfield',
    ]);
    $fields['executed'] = BaseFieldDefinition::create('integer')
    ->setLabel(t('Task executed time'))
    ->setDescription(t('Task executed time in second'))
    ->setRequired(false)
    ->setDefaultValue(0)
    ->setDisplayOptions('form', [
      'type' => 'string_textfield',
    ]);
    
    return $fields;
  }
  /**
   * 
   * @return unknown|NULL
   */
  public function execute(){
    
    try {
      $this->task_status->value = self::TASK_STATUS_PROCESS;
      $this->save();
      $start = time();
      
      $parts = explode('_',$this->bundle());
      $context_type = $parts[0];
      $context = empty($this->{$context_type}->entity)?NULL:$this->{$context_type}->entity;
      
      $args = Json::decode($this->arguments->value);
      $options = Json::decode($this->options->value);
      
      $result = ProvisionUI::provisionCommand($this->task_type->value,$context,$options,$args);
      
      if($result['status_code']){
        $this->task_status->value = self::TASK_STATUS_FAILURE;
      }else{
        $this->task_status->value = self::TASK_STATUS_SUCCESS;
      }
      $end = time();
      $this->executed->value = $end - $start;
      $this->save();
    }catch (\Exception $e){
      watchdog_exception("provision task", $e);
    }
  }
}

