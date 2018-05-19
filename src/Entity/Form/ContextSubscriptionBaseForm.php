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
use Drupal\provision_ui\ProvisionUI;
use Drupal\provision_ui\Entity\ServiceSubscriptionConfig;

abstract class ContextSubscriptionBaseForm extends EckEntityForm {
  
  public abstract function getContextType();
  
  public function getHiddenProperties(){
    return [];
  }
  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    
    $form = parent::form($form, $form_state);
    
    $context_type = $this->getContextType();
    $form['advanced'] = [
      '#type' => 'vertical_tabs',
      '#weight' => 99,
    ];
    
    $services = Context::getServiceOptions();
    foreach (Context::getClassName($context_type)::serviceRequirements() as $service){
      $form[$service] = [
        '#type' => 'details',
        '#title' => t($services[$service]),
        '#group' => 'advanced',
        '#tree' => true,
      ];
      $servers = ProvisionUI::serverList($service);
      $options = [];
      foreach ($servers as $key => $item){
        $options[$key] = $item->label();
      }
      $form[$service]['server'] = [
        '#type' => 'radios',
        '#options' => $options,
        '#required' => TRUE
      ];
      foreach ($servers as $key => $item){
        if($type = $item->getService($service)){
          $method = "{$context_type}_options";
          $service_properties = Service::getClassName($service,$type->getType())::{$method}();
          if(!empty($service_properties)){
            $form[$service][$key] = [
              '#type' => 'details',
              '#open' => true,
              '#title' => t("@server Settings",['@server' => $item->label()]),
              '#states' => [
                'visible' => [
                  ':input[name="' . $service .'[server]"]' => array('value' => $key),
                ]
              ]
            ];
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
      }
    }
    foreach ($this->entity->service_subscriptions as $item){
      if(!empty($item->entity)){
        $service = $item->entity->getService();
        $server = $item->entity->getServer();
        $form[$service]['server']['#default_value'] = $server;
        $properties = $item->entity->getProperties();
        foreach ($properties as $key => $value){
          $form[$service][$server][$key]['#default_value'] = $value;
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
    $context_type = $this->getContextType();
    
    foreach (Context::getClassName($context_type)::serviceRequirements() as $key){
      if(!empty($values[$key]['server'])){
        $id = $context_type . '_' . $values['machine_name'][0]['value'] . '_' . $key;
        $service = \Drupal::entityTypeManager()->getStorage('provision_subscription')->load($id);
        if($service){
          $service->setService($key);
          $service->setServer($values[$key]['server']);
          $service->setProperties($values[$key][$values[$key]['server']]);
          $service->save();
        }else{
          $data = [
            'id' => $id,
            'service' => $key,
            'server' => $values[$key]['server'],
            'properties' => isset($values[$key][$values[$key]['server']])?$values[$key][$values[$key]['server']]:[],
          ];
          $service = new ServiceSubscriptionConfig($data, 'provision_subscription');
          $service->save();
          $this->entity->service_subscriptions[] = $service;
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

