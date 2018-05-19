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

class ContextEckEntity extends EckEntity {

  /**
   *
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {
    $fields = parent::baseFieldDefinitions($entity_type);
    
    $fields['machine_name'] = BaseFieldDefinition::create('string')
    ->setLabel(t('Machine name'))
    ->setDescription(t('Machine name of the ' . $entity_type->getLabel()))
    ->setRequired(TRUE)
    ->setSetting('max_length', 64)
    ->addConstraint('UniqueField', [])
    ->setDisplayOptions('form', [
      'type' => 'machine_name_widget',
      'weight' => -4,
      'settings' => [
        'source' => [
          'title',
          'widget',
          0,
          'value',
        ],
        'exists' => ['\Drupal\provision_ui\Entity\ContextEckEntity','exists'],
      ],
    ]);
    
    $class = Context::getClassName($entity_type->id());
    $method = "option_documentation";
    /**
     * @var Property $property
     */
    foreach ($class::{$method}() as $key => $property) {
      $fields[$key] = BaseFieldDefinition::create('string')
      ->setLabel(t(static::key2Name($key)))
      ->setDescription(t($property->description))
      ->setDefaultValue($property->default)
      ->setRequired($property->required)
      ->setSetting('max_length', 255)
      ->setDisplayOptions('form', [
        'type' => 'string_textfield',
      ]);
    }
    return $fields;
  }
  /**
   * Checks that an existing machine name does not already exist.
   *
   * This is a static mehod so it can be used by a machine name field.
   *
   * @param string $machine_name
   *   The machine name to load the entity by.
   *
   * @return \Drupal\colossal_menu\Entity\Link|NULL
   *   Loaded Link entity or NULL if not found.
   */
  public static function exists($machine_name, $element, FormStateInterface $form_state) {
    $entity = $form_state->getFormObject()->getEntity();
    $storage = \Drupal::entityTypeManager()->getStorage($entity->getEntityTypeId());
    $result = $storage->getQuery()
    ->condition('machine_name', $machine_name)
    ->execute();
    return $result ? $storage->loadMultiple($result) : [];
  }
  public static function key2Name($key) {
    $name = str_replace('_', ' ', $key);
    return ucwords($name);
  }
  /**
   * 
   * @param unknown $type
   * @return ServiceConfig|NULL
   */
  public function getService($type){
    foreach ($this->services as $service){
      if(!empty($service->entity) && $service->entity->getService() == $type){
        return $service->entity;
      }
    }
    return NULL;
  }
}

