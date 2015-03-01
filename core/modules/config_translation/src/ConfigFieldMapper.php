<?php

/**
 * @file
 * Contains \Drupal\config_translation\ConfigFieldMapper.
 */

namespace Drupal\config_translation;

use Drupal\Core\Entity\EntityInterface;

/**
 * Configuration mapper for fields.
 *
 * On top of plugin definition values on ConfigEntityMapper, the plugin
 * definition for field mappers are required to contain the following
 * additional keys:
 * - base_entity_type: The name of the entity type the fields are attached to.
 */
class ConfigFieldMapper extends ConfigEntityMapper {

  /**
   * Loaded entity instance to help produce the translation interface.
   *
   * @var \Drupal\field\FieldConfigInterface
   */
  protected $entity;

  /**
   * {@inheritdoc}
   */
  public function getBaseRouteParameters() {
    $parameters = parent::getBaseRouteParameters();
    $base_entity_info = $this->entityManager->getDefinition($this->pluginDefinition['base_entity_type']);
    $parameters[$base_entity_info->getBundleEntityType()] = $this->entity->getTargetBundle();
    return $parameters;
  }

  /**
   * {@inheritdoc}
   */
  public function getOverviewRouteName() {
    return 'entity.field_config.config_translation_overview.' . $this->pluginDefinition['base_entity_type'];
  }

  /**
   * {@inheritdoc}
   */
  public function getTypeLabel() {
    $base_entity_info = $this->entityManager->getDefinition($this->pluginDefinition['base_entity_type']);
    return $this->t('@label fields', array('@label' => $base_entity_info->getLabel()));
  }

  /**
   * {@inheritdoc}
   */
  public function setEntity(EntityInterface $entity) {
    if (parent::setEntity($entity)) {

      // Field storage config can also contain translatable values. Add the name
      // of the config as well to the list of configs for this entity.
      $field_storage = $entity->getFieldStorageDefinition();
      $entity_type_info = $this->entityManager->getDefinition($field_storage->getEntityTypeId());
      $this->addConfigName($entity_type_info->getConfigPrefix()  . '.' . $field_storage->id());
      return TRUE;
    }
    return FALSE;
  }

}
