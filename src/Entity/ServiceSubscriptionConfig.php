<?php

namespace Drupal\provision_ui\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBase;

/**
 * Defines the ServiceConfig entity.
 *
 * @ConfigEntityType(
 *   id = "provision_subscription",
 *   label = @Translation("Provision Service Subscription"),
 *   config_prefix = "service_subscription",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "id",
 *     "uuid" = "uuid",
 *   },
 * )
 */
class ServiceSubscriptionConfig extends ConfigEntityBase {
  
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
   * Server machine name.
   * @var string
   */
  public $server;
  
  /**
   * Service properties.
   * @var array
   */
  public $properties = [];
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
   * @return the $server
   */
  public function getServer() {
      return $this->server;
  }
  
  /**
   * @return the $properties
   */
  public function getProperties() {
      return $this->properties;
  }
  
  /**
   * @param string $server
   */
  public function setServer($server) {
      $this->server = $server;
  }
  
  /**
   * @param array $properties
   */
  public function setProperties($properties) {
      $this->properties = $properties;
  }

}

