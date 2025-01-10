<?php declare(strict_types = 1);

namespace Drupal\openai_assistant\Form;

use Drupal\Core\Entity\EntityForm;
use Drupal\Core\Form\FormStateInterface;
use Drupal\openai_assistant\Entity\Assistant;

/**
 * Assistant form.
 */
final class AssistantForm extends EntityForm {

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
      '#type' => 'machine_name',
      '#default_value' => $this->entity->id(),
      '#machine_name' => [
        'exists' => [Assistant::class, 'load'],
      ],
      '#disabled' => !$this->entity->isNew(),
    ];

    $form['status'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Enabled'),
      '#default_value' => $this->entity->status(),
    ];

    $form['description'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Description'),
      '#default_value' => $this->entity->get('description'),
    ];

    $form['model'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Model'),
      '#maxlength' => 255,
      '#default_value' => $this->entity->get('model'),
      '#required' => TRUE,
    ];

    $form['temperature'] = [
      '#type' => 'number',
      '#title' => $this->t('Temperature'),
      '#default_value' => $this->entity->get('temperature'),
      '#min' => 0.0,
      '#max' => 2.0,
      '#step' => 0.1,
      '#required' => TRUE,
    ];

    $form['topP'] = [
      '#type' => 'number',
      '#title' => $this->t('TopP'),
      '#default_value' => $this->entity->get('topP'),
      '#min' => 0.0,
      '#max' => 1.0,
      '#step' => 0.1,
      '#required' => TRUE,
    ];

    $form['system_instructions'] = [
      '#type' => 'textarea',
      '#title' => $this->t('System Instructions'),
      '#default_value' => $this->entity->get('system_instructions'),
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