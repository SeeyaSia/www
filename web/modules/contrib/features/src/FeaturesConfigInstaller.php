<?php

namespace Drupal\features;

use Drupal\Core\Config\ConfigInstaller;
use Drupal\Core\Config\ConfigInstallerInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Config\StorageInterface;
use Drupal\Core\Config\TypedConfigManagerInterface;
use Drupal\Core\Config\ConfigManagerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Class for customizing the test for pre existing configuration.
 *
 * Decorates the ConfigInstaller with findPreExistingConfiguration() modified
 * to allow Feature modules to be installed.
 */
class FeaturesConfigInstaller extends ConfigInstaller {

  /**
   * The configuration installer.
   *
   * @var \Drupal\Core\Config\ConfigInstallerInterface
   */
  protected $configInstaller;

  /**
   * The features manager.
   *
   * @var \Drupal\features\FeaturesManagerInterface
   */
  protected $featuresManager;

  /**
   * Constructs the configuration installer.
   *
   * @param \Drupal\Core\Config\ConfigInstallerInterface $config_installer
   *    The configuration installer.
   * @param \Drupal\features\FeaturesManagerInterface $features_manager
   *    The features manager.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The configuration factory.
   * @param \Drupal\Core\Config\StorageInterface $active_storage
   *   The active configuration storage.
   * @param \Drupal\Core\Config\TypedConfigManagerInterface $typed_config
   *   The typed configuration manager.
   * @param \Drupal\Core\Config\ConfigManagerInterface $config_manager
   *   The configuration manager.
   * @param \Symfony\Component\EventDispatcher\EventDispatcherInterface $event_dispatcher
   *   The event dispatcher.
   */
  public function __construct(ConfigInstallerInterface $config_installer,
    FeaturesManagerInterface $features_manager,
    ConfigFactoryInterface $config_factory,
    StorageInterface $active_storage,
    TypedConfigManagerInterface $typed_config,
    ConfigManagerInterface $config_manager,
    EventDispatcherInterface $event_dispatcher) {
    $this->configInstaller = $config_installer;
    $this->featuresManager = $features_manager;

    // In order to be compatible with both core 8.2.x and 8.3.x, this
    // constructor cannot require being passed the install profile from its
    // service definition because the %install_profile% parameter does not
    // exist before 8.3, so it has to request it.
    $install_profile = drupal_get_profile();

    parent::__construct($config_factory, $active_storage, $typed_config, $config_manager, $event_dispatcher, $install_profile);
  }

  /**
   * {@inheritdoc}
   */
  protected function findPreExistingConfiguration(StorageInterface $storage) {
    // Override
    // Drupal\Core\Config\ConfigInstaller::findPreExistingConfiguration().
    // Allow config that already exists coming from Features.
    $features_config = array_keys($this->featuresManager->listExistingConfig());
    // Map array so we can use isset instead of in_array for faster access.
    $features_config = array_combine($features_config, $features_config);
    $existing_configuration = array();
    // Gather information about all the supported collections.
    $collection_info = $this->configManager->getConfigCollectionInfo();

    foreach ($collection_info->getCollectionNames() as $collection) {
      $config_to_create = array_keys($this->getConfigToCreate($storage, $collection));
      $active_storage = $this->getActiveStorages($collection);
      foreach ($config_to_create as $config_name) {
        if ($active_storage->exists($config_name)) {
          // Test if config is part of a Feature package.
          if (!isset($features_config[$config_name])) {
            $existing_configuration[$collection][] = $config_name;
          }
        }
      }
    }
    return $existing_configuration;
  }

}