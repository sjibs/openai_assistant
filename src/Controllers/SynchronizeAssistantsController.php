<?php declare(strict_types = 1);

namespace Drupal\openai_assistant\Controllers;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\openai_assistant\Services\ApiClient;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * Controller for synchronizing assistants.
 */
class SynchronizeAssistantsController extends ControllerBase {

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
  protected $entityTypeManager;

  /**
   * Constructs a new SynchronizeAssistantsController object.
   *
   * @param \Drupal\openai_assistant\Services\ApiClient $api_client
   *   The API client service.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager service.
   */
  public function __construct(ApiClient $api_client, EntityTypeManagerInterface $entity_type_manager) {
    $this->apiClient = $api_client;
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container): self {
    return new static(
      $container->get('openai_assistant.api_client'),
      $container->get('entity_type.manager')
    );
  }

  /**
   * Synchronizes the assistants.
   *
   * @return \Symfony\Component\HttpFoundation\RedirectResponse
   *   A redirect response.
   */
  public function synchronize(): RedirectResponse {
    // Fetch the assistants from the remote API.
    $remote_assistants = $this->apiClient->getAssistants();
    $remote_assistant_ids = array_column($remote_assistants, 'id');

    // Fetch the local assistants.
    $local_assistants = $this->entityTypeManager->getStorage('assistant')->loadMultiple();

    // Check for local assistants not present in the remote.
    foreach ($local_assistants as $local_assistant) {
      if (!in_array($local_assistant->id(), $remote_assistant_ids)) {
        // Create the assistant on the remote API.
        $response = $this->apiClient->createAssistant([
          'name' => $local_assistant->label(),
          'description' => $local_assistant->get('description'),
          'model' => $local_assistant->get('model'),
          'temperature' => (float) $local_assistant->get('temperature'),
          'top_p' => (float) $local_assistant->get('topP'),
          'instructions' => $local_assistant->get('system_instructions'),
        ]);

        // Update the local assistant with the ID returned by the API call.
        $local_assistant->set('id', $response['id']);
        $local_assistant->save();

        $this->messenger()->addStatus($this->t('Assistant %label has been synchronized to the OpenAI platform with the new ID %id.', [
          '%label' => $local_assistant->label(),
          '%id' => $response['id'],
        ]));
      }
    }

    return $this->redirect('entity.assistant.collection');
  }

}