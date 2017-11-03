<?php

namespace Drupal\webform_permissions_by_term\Service;

use Drupal\Core\Database\Connection;
use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityManagerInterface;
use Drupal\webform_permissions_by_term\Event\EntityFieldValueAccessDeniedEvent;
use Drupal\permissions_by_term\Service\AccessCheck;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Class WebformAccessChecker.
 *
 * @package Drupal\webform_permissions_by_term\Service
 */
class WebformAccessChecker extends AccessCheck implements WebformAccessCheckerInterface {

  /**
   * The event dispatcher.
   *
   * @var \Symfony\Component\EventDispatcher\EventDispatcherInterface
   */
  private $eventDispatcher;

  /**
   * The cache for checked entities.
   *
   * @var \Drupal\webform_permissions_by_term\Service\CheckedEntityCache
   */
  private $checkedEntityCache;

  /**
   * The entity field value access denied event.
   *
   * @var \Drupal\webform_permissions_by_term\Event\EntityFieldValueAccessDeniedEvent
   */
  private $event;

  /**
   * WebformAccessChecker constructor.
   *
   * We override the constructor, because we do not need the entity manager.
   *
   * @param \Symfony\Component\EventDispatcher\EventDispatcherInterface $event_dispatcher
   *   The event dispatcher.
   * @param \Drupal\webform_permissions_by_term\Service\CheckedEntityCache $checked_entity_cache
   *   The cache for checked entities.
   * @param \Drupal\Core\Entity\EntityManagerInterface $entity_manager
   *   The core entity type manager.
   * @param \Drupal\Core\Database\Connection $database
   *   The database connection.
   */
  public function __construct(
    EventDispatcherInterface $event_dispatcher,
    CheckedEntityCache $checked_entity_cache,
    EntityManagerInterface $entity_manager,
    Connection $database
  ) {
    parent::__construct($database);
    $this->eventDispatcher = $event_dispatcher;
    $this->checkedEntityCache = $checked_entity_cache;

    $this->event = new EntityFieldValueAccessDeniedEvent();
  }

  /**
   * {@inheritdoc}
   */
  public function isWebformAccessAllowed(ContentEntityInterface $entity, $uid = FALSE) {
    if ($entity->getEntityTypeId() == 'webform_submission') {
      $permissions_by_term = NULL;

      $permissions_by_term = $entity->getData(WEBFORM_SECURITY_ELEMENT_NAME);

      // TODO Extend this so that user can select multiple terms.
      // currently it will only work if the single term is selected.
      if ($permissions_by_term != NULL) {
        if (!$this->isAccessAllowedByDatabase($permissions_by_term, $uid)) {
          // Return that the user is not allowed to access this entity.
          return FALSE;
        }
        return TRUE;
      }

    }
  }

}
