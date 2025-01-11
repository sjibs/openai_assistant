<?php declare(strict_types = 1);

namespace Drupal\openai_assistant;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Config\Entity\ConfigEntityListBuilder;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\openai_assistant\Services\ApiClient;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\Core\Url;

/**
 * Provides a listing of assistants.
 */
final class AssistantListBuilder extends ConfigEntityListBuilder {

  /**
   * The API client service.
   *
   * @var \Drupal\openai_assistant\Services\ApiClient
   */
  protected ApiClient $apiClient;

  /**
   * The entity type manager service.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected EntityTypeManagerInterface $entityTypeManager;

  /**
   * The messenger service.
   *
   * @var \Drupal\Core\Messenger\MessengerInterface
   */
  protected $messenger;

  /**
   * Constructs a new AssistantListBuilder object.
   *
   * @param \Drupal\Core\Entity\EntityTypeInterface $entity_type
   *   The entity type.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager service.
   * @param \Drupal\openai_assistant\Services\ApiClient $api_client
   *   The API client service.
   * @param \Drupal\Core\Messenger\MessengerInterface $messenger
   *   The messenger service.
   */
  public function __construct(EntityTypeInterface $entity_type, EntityTypeManagerInterface $entity_type_manager, ApiClient $api_client, MessengerInterface $messenger) {
    $storage = $entity_type_manager->getStorage($entity_type->id());
    parent::__construct($entity_type, $storage);
    $this->entityTypeManager = $entity_type_manager;
    $this->apiClient = $api_client;
    $this->messenger = $messenger;
  }

  /**
   * {@inheritdoc}
   */
  public static function createInstance(ContainerInterface $container, EntityTypeInterface $entity_type): self {
    return new static(
      $entity_type,
      $container->get('entity_type.manager'),
      $container->get('openai_assistant.api_client'),
      $container->get('messenger')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function buildHeader(): array {
    $header['label'] = $this->t('Label');
    $header['id'] = $this->t('Machine name');
    $header['status'] = $this->t('Status');

    // Fetch the assistants from the remote API.
    $remote_assistants = $this->apiClient->getAssistants();
    $remote_assistant_ids = array_column($remote_assistants, 'id');

    // Fetch the local assistants.
    $local_assistants = $this->entityTypeManager->getStorage('assistant')->loadMultiple();

    $has_error = false;
    // Check for local assistants not present in the remote.
    foreach ($local_assistants as $local_assistant) {
      if (!in_array($local_assistant->id(), $remote_assistant_ids)) {
        $has_error = true;
        $this->messenger->addError($this->t('Assistant %label (ID: %id) is present locally but not on the OpenAI platform.', [
          '%label' => $local_assistant->label(),
          '%id' => $local_assistant->id(),
        ]));
      }
    }
    if ($has_error) {
      $synchronize_url = Url::fromRoute('openai_assistant.synchronize_assistants')->toString();
      $this->messenger->addError($this->t('<a href=":url">Click Here</a> to re-synchronize your assistants.', [
        ':url' => $synchronize_url,
      ]));
    }

    return $header + parent::buildHeader();
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity): array {
    /** @var \Drupal\openai_assistant\AssistantInterface $entity */
    $row['label'] = $entity->label();
    $row['id'] = $entity->id();
    $row['status'] = $entity->status() ? $this->t('Enabled') : $this->t('Disabled');
    return $row + parent::buildRow($entity);
  }

}