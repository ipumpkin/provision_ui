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

class ContextServerForm extends EckEntityForm {

  public function getHiddenProperties(){
    return [
      'context_class',
    ];
  }
  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    
    $form = parent::form($form, $form_state);
    
    $form['advanced'] = [
      '#type' => 'vertical_tabs',
      '#weight' => 99,
    ];
    
    foreach (Context::getServiceOptions() as $service => $name){
      $form[$service] = [
        '#type' => 'details',
        '#title' => t($name),
        '#group' => 'advanced',
        '#tree' => true,
      ];
      $options = Context::getServiceTypeOptions($service);
      $form[$service]['type'] = [
        '#type' => 'radios',
        '#options' => array_merge(['_none' => t('None')],$options),
        '#default_value' => '_none'
      ];
      foreach ($options as $key => $label){
        $form[$service][$key] = [
          '#type' => 'details',
          '#open' => true,
          '#title' => t("{$label} Settings"),
          '#states' => [
            'visible' => [
              ':input[name="' . $service .'[type]"]' => array('value' => $key),
            ]
          ]
        ];
        $service_properties = Service::getClassName($service,$key)::server_options();
        foreach ($service_properties as $p => $define){
          if($define instanceof Property){
            $form[$service][$key][$p] = [
              '#type' => 'textfield',
              '#title' => t(ContextEckEntity::key2Name($p)),
              '#default_value' => $define->default,
              '#description' => t($define->description)
            ];
          }else{
            $form[$service][$key][$p] = [
              '#type' => 'textfield',
              '#title' => t(ContextEckEntity::key2Name($p)),
              '#description' => t($define)
            ];
          }
          
        }
      }
    }
    foreach ($this->entity->services as $item){
      if(!empty($item->entity)){
        $service = $item->entity->getService();
        $type = $item->entity->getType();
        $form[$service]['type']['#default_value'] = $type;
        $properties = $item->entity->getProperties();
        foreach ($properties as $key => $value){
          $form[$service][$type][$key]['#default_value'] = $value;
        }
      }
    }
    
    foreach ($this->getHiddenProperties() as $field){
      $form[$field]['#access'] = false;
    }
    return $form;
  }
  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    parent::submitForm($form, $form_state);
    
    $values = $form_state->getValues();
    foreach (Context::getServiceOptions() as $key => $name){
      if(!empty($values[$key]['type']) && $values[$key]['type'] != '_none'){
        $id = $values['machine_name'][0]['value'] . '_' . $key;
        $service = \Drupal::entityTypeManager()->getStorage('provision_service')->load($id);
        if($service){
          $service->setType($values[$key]['type']);
          $service->setProperties($values[$key][$values[$key]['type']]);
          $service->save();
          foreach ($this->entity->services as $item){
            if($item->target_id == $service->id()){
              continue;
            }
          }
          $this->entity->services[] = $service;
        }else{
          $data = [
            'id' => $id,
            'service' => $key,
            'type' => $values[$key]['type'],
            'properties' => $values[$key][$values[$key]['type']],
          ];
          $service = new ServiceConfig($data, 'provision_service');
          $service->save();
          $this->entity->services[] = $service;
        }
      }
    }
  }
  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    parent::save($form, $form_state);
  }
}

