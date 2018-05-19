<?php

namespace Drupal\provision_ui\Entity\Form;

use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\eck\Entity\EckEntity;
use Drupal\Core\Field\BaseFieldDefinition;
use Aegir\Provision\Context;
use Drupal\Core\DependencyInjection\ClassResolver;
use Aegir\Provision\Property;
use Drupal\eck\Form\Entity\EckEntityForm;
use Drupal\Core\Form\FormStateInterface;
use Aegir\Provision\Service;
use Drupal\provision_ui\Entity\ContextEckEntity;
use Drupal\provision_ui\Entity\ServiceConfig;

class ContextPlatformForm extends ContextSubscriptionBaseForm {
  
  public function getContextType(){
    return 'platform';
  }
  
  public function getHiddenProperties(){
    return [];
  }
}

