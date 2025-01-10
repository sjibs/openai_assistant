<?php declare(strict_types = 1);

namespace Drupal\openai_assistant\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Form for importing an assistant from the web.
 */
final class ImportAssistantForm extends FormBase {

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
    $form['example'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Assistant URL'),
      '#description' => $this->t('Enter the URL to import the assistant from.'),
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

    $example = $form_state->getValue('example');

    $this->messenger()->addStatus(message: $this->t('Assistant imported %example', ['%example' => $example]));
  }

}