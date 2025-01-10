<?php declare(strict_types = 1);

namespace Drupal\openai_assistant\Form;

use Drupal\Core\Entity\EntityForm;
use Drupal\Core\Form\FormStateInterface;
use Drupal\openai_assistant\Entity\Assistant;
use Drupal\openai_assistant\Services\ApiClient;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Assistant form.
 */
final class AssistantForm extends EntityForm {

  /**
   * The API client service.
   *
   * @var \Drupal\openai_assistant\Services\ApiClient
   */
  protected ApiClient $apiClient;

  /**
   * Constructs an AssistantForm object.
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
  public function form(array $form, FormStateInterface $form_state): array {
    $form = parent::form($form, $form_state);

    $form['label'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Label'),
      '#maxlength' => 255,
      '#default_value' => $this->entity->label(),
      '#required' => TRUE,
    ];

    $form['id'] = [
      '#type' => 'textfield',
      '#default_value' => $this->entity->id(),
      '#title' => $this->t('Assistant ID'),
      '#machine_name' => [
        'exists' => [Assistant::class, 'load'],
      ],
      '#disabled' => !$this->entity->isNew(),
    ];

    $form['description'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Admin Description'),
      '#default_value' => $this->entity->get('description'),
    ];

    // Fetch the models from the API client.
    $models = $this->apiClient->getModels();
    $model_options = [];
    foreach ($models as $model) {
      $model_options[$model['id']] = $model['id'];
    }

    $form['model'] = [
      '#type' => 'select',
      '#title' => $this->t('Model'),
      '#options' => $model_options,
      '#default_value' => $this->entity->get('model'),
      '#required' => TRUE,
      '#description' => $this->t('GPT-4o mini is recommended as it\'s typically cheaper to run.'),
    ];

    $form['system_instructions'] = [
      '#type' => 'textarea',
      '#title' => $this->t('System Instructions'),
      '#default_value' => $this->entity->get('system_instructions'),
    ];

    $form['generation_settings'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Generation Settings'),
    ];

    $form['generation_settings']['temperature'] = [
      '#type' => 'number',
      '#title' => $this->t('Temperature'),
      '#default_value' => $this->entity->get('temperature'),
      '#min' => 0.0,
      '#max' => 2.0,
      '#step' => 0.1,
      '#required' => TRUE,
      '#description' => $this->t('Controls the randomness of the model\'s output. Lower values produce more deterministic results.'),
    ];

    $form['generation_settings']['topP'] = [
      '#type' => 'number',
      '#title' => $this->t('Top-p'),
      '#default_value' => $this->entity->get('topP'),
      '#min' => 0.0,
      '#max' => 1.0,
      '#step' => 0.1,
      '#required' => TRUE,
      '#description' => $this->t('Limits the cumulative probability of tokens considered for sampling. Lower values produce more deterministic results.'),
    ];

    $form['status'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Enabled'),
      '#default_value' => $this->entity->status(),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state): int {
    $result = parent::save($form, $form_state);
    $message_args = ['%label' => $this->entity->label()];
    $this->messenger()->addStatus(
      match($result) {
        \SAVED_NEW => $this->t('Created new assistant %label.', $message_args),
        \SAVED_UPDATED => $this->t('Updated assistant %label.', $message_args),
      }
    );
    $form_state->setRedirectUrl($this->entity->toUrl('collection'));
    return $result;
  }

}