<?php

namespace Drupal\provision_ui\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBase;

/**
 * Defines the ServiceConfig entity.
 *
 * @ConfigEntityType(
 *   id = "provision_service",
 *   label = @Translation("Provision Service"),
 *   config_prefix = "service",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "id",
 *     "uuid" = "uuid",
 *   },
 * )
 */
class ServiceConfig extends ConfigEntityBase {
  
  /**
   * The ID.
   *
   * @var string
   */
  public $id;
  
  /**
   * Service type.
   * @var string
   */
  public $service;
  
  /**
   * Service sub type.
   * @var string
   */
  public $type;
  
  /**
   * Service properties.
   * @var array
   */
  public $properties = [];
  /**
   * @return the $type
   */
  public function getType() {
      return $this->type;
  }
  
  /**
   * @return the $service
   */
  public function getService() {
      return $this->service;
  }
  
  /**
   * @param string $service
   */
  public function setService($service) {
      $this->service = $service;
  }
  
  /**
   * @return the $properties
   */
  public function getProperties() {
      return $this->properties;
  }
  
  /**
   * @param string $type
   */
  public function setType($type) {
      $this->type = $type;
  }
  
  /**
   * @param array $properties
   */
  public function setProperties($properties) {
      $this->properties = $properties;
  }
  
}

