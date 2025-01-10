<?php declare(strict_types = 1);

namespace Drupal\openai_assistant\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Configure OpenAI-Assistant settings for this site.
 */
final class SettingsForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId(): string {
    return 'openai_assistant_settings';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames(): array {
    return ['openai_assistant.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state): array {
    $config = $this->config('openai_assistant.settings');

    $form['openai_secret_key'] = [
      '#type' => 'textfield',
      '#title' => $this->t('OpenAI Secret Key'),
      '#default_value' => $config->get('openai_secret_key') ?: getenv('OPENAI_SECRET_KEY'),
      '#description' => $this->t('Enter your OpenAI secret key. You can also set this using the OPENAI_SECRET_KEY environment variable.'),
      '#maxlength'  =>512,
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state): void {
    // Add validation for the OpenAI secret key if necessary.
    parent::validateForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state): void {
    $this->config('openai_assistant.settings')
      ->set('openai_secret_key', $form_state->getValue('openai_secret_key'))
      ->save();
    parent::submitForm($form, $form_state);
  }

}