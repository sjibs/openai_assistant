<?php declare(strict_types = 1);

namespace Drupal\openai_assistant\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\openai_assistant\Services\ApiClient;
use Drupal\openai_assistant\Entity\Assistant;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Form for importing an assistant from the web.
 */
final class ImportAssistantForm extends FormBase {

  /**
   * The API client service.
   *
   * @var \Drupal\openai_assistant\Services\ApiClient
   */
  protected ApiClient $apiClient;

  /**
   * Constructs an ImportAssistantForm object.
   *
   * @param \Drupal\openai_assistant\Services\ApiClient $api_client
   *   The API client service.
   */
  public function __construct(ApiClient $api_client) {
    $this->apiClient = $api_client;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container): self {
    return new static(
      $container->get('openai_assistant.api_client')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId(): string {
    return 'import_assistant_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state): array {
    // Fetch the assistants from the API client.
    $assistants = $this->apiClient->getAssistants();

    $assistant_options = [];
    foreach ($assistants as $assistant) {
      $assistant_options[$assistant['id']] = $assistant['name'] ?? $assistant['id'];
    }

    $form['assistant_id'] = [
      '#type' => 'select',
      '#title' => $this->t('Assistant'),
      '#options' => $assistant_options,
      '#description' => $this->t('Select the assistant to import.'),
      '#required' => TRUE,
    ];

    $form['actions']['#type'] = 'actions';
    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Import'),
      '#button_type' => 'primary',
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state): void {
    $assistant_id = $form_state->getValue('assistant_id');

    // Fetch the assistants from the API client.
    $assistants = $this->apiClient->getAssistants();

    // Find the selected assistant.
    $selected_assistant = null;
    foreach ($assistants as $assistant) {
      if ($assistant['id'] === $assistant_id) {
        $selected_assistant = $assistant;
        break;
      }
    }

    if ($selected_assistant) {
      // Check if the assistant already exists in the configuration.
      $existing_assistant = Assistant::load($assistant_id);
      if ($existing_assistant) {
        $this->messenger()->addError($this->t('The assistant %assistant_id has already been imported.', ['%assistant_id' => $assistant_id]));
      } else {
        // Create a new assistant configuration entity.
        $assistant_entity = Assistant::create([
          'id' => $selected_assistant['id'],
          'label' => $selected_assistant['name'],
          'description' => $selected_assistant['description'],
          'model' => $selected_assistant['model'],
          'temperature' => $selected_assistant['temperature'],
          'topP' => $selected_assistant['top_p'],
          'system_instructions' => $selected_assistant['instructions'],
          'project_id' => '', // Assuming project_id is not provided in the response.
        ]);
        $assistant_entity->save();

        $this->messenger()->addStatus($this->t('Assistant imported: %assistant_id', ['%assistant_id' => $assistant_id]));
      }
    } else {
      $this->messenger()->addError($this->t('The selected assistant could not be found.'));
    }
  }

}