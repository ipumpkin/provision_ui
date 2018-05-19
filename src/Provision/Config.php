<?php

namespace Drupal\provision_ui\Provision;

use Aegir\Provision\Console\Config as ConfigBase;
use Aegir\Provision\Console\ProvisionStyle;
use Aegir\Provision\Provision;

class Config  extends ConfigBase {
  
  /**
   * DefaultsConfig constructor.
   */
  public function __construct(ProvisionStyle $io = null, $validate = TRUE)
  {
    parent::__construct($io, FALSE);
    
    $provision_uri = file_default_scheme() . "://provision";
    $provision_config_uri = $provision_uri . DIRECTORY_SEPARATOR . 'config';
    $provision_contexts_uri = $provision_config_uri . DIRECTORY_SEPARATOR . Provision::CONTEXTS_PATH;
    file_prepare_directory($provision_uri,FILE_CREATE_DIRECTORY);
    file_prepare_directory($provision_config_uri,FILE_CREATE_DIRECTORY);
    file_prepare_directory($provision_contexts_uri,FILE_CREATE_DIRECTORY);
    
    $this->set('aegir_root', \Drupal::service('file_system')->realpath($provision_uri));
    $this->set('config_path', \Drupal::service('file_system')->realpath($provision_config_uri));
    $this->set('contexts_path', \Drupal::service('file_system')->realpath($provision_contexts_uri));
    
    if ($validate) {
      $this->validateConfig();
    }
  }
}