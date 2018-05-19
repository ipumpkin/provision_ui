<?php

namespace Drupal\provision_ui\Provision;

use Aegir\Provision\Provision as ProvisionBase;
use Aegir\Provision\Console\Config;
use Aegir\Provision\Console\ConsoleOutput;
use Robo\Runner as RoboRunner;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Logger\ConsoleLogger;
use Symfony\Component\Console\Output\OutputInterface;
use Drupal\provision_ui\Robo\ProvisionUIRobo;
use Aegir\Provision\Application;

class Provision extends ProvisionBase {

  /**
   *
   * @param Config $config
   * @param InputInterface $input
   * @param OutputInterface $output
   */
  public function __construct(Config $config, InputInterface $input = NULL, OutputInterface $output = NULL) {
    $logger = new ConsoleLogger($output);
    $this->setLogger($logger);
    
    $this->setConfig($config);
    
    // Create Application.
    $application = new Application(self::APPLICATION_NAME, self::VERSION);
    $application->setProvision($this)->setLogger($logger);
    $application->configureIO($input, $output);
    $this->setInput($input);
    $this->setOutput($output);
    
    // Create and configure container.
    $container = ProvisionUIRobo::createNewContainer($input, $output, $application, $config);
    $this->setContainer($container);
    $this->configureContainer($container);
    
    // Instantiate Robo Runner.
    $this->runner = new RoboRunner();
    
    $this->runner->setContainer($container);
    $this->runner->setSelfUpdateRepository(self::REPOSITORY);
    
    $this->setBuilder($container->get('builder'));
    $this->setLogger($container->get('logger'));
    
    $this->tasks = $container->get('tasks');
    $this->console = new ConsoleOutput($output->getVerbosity());
    $this->console->setProvision($this);
    
    $this->loadAllContexts();
    
    try {
      $this->activeContext = $this->getContext($input->activeContextName);
    } catch ( \Exception $e ) {
    }
  }

  /**
   *
   * {@inheritdoc}
   * @see \Aegir\Provision\Provision::run()
   */
  public function run(InputInterface $input, OutputInterface $output) {
    $status_code = parent::run($input, $output);
    ProvisionUIRobo::resetConatiner();
    return $status_code;
  }
}