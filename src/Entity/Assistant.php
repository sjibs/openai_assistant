<?php declare(strict_types = 1);

namespace Drupal\openai_assistant\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBase;
use Drupal\openai_assistant\AssistantInterface;

/**
 * Defines the assistant entity type.
 *
 * @ConfigEntityType(
 *   id = "assistant",
 *   label = @Translation("Assistant"),
 *   label_collection = @Translation("Assistants"),
 *   label_singular = @Translation("assistant"),
 *   label_plural = @Translation("assistants"),
 *   label_count = @PluralTranslation(
 *     singular = "@count assistant",
 *     plural = "@count assistants",
 *   ),
 *   handlers = {
 *     "list_builder" = "Drupal\openai_assistant\AssistantListBuilder",
 *     "form" = {
 *       "add" = "Drupal\openai_assistant\Form\AssistantForm",
 *       "edit" = "Drupal\openai_assistant\Form\AssistantForm",
 *       "delete" = "Drupal\Core\Entity\EntityDeleteForm",
 *     },
 *   },
 *   config_prefix = "assistant",
 *   admin_permission = "administer assistant",
 *   links = {
 *     "collection" = "/admin/structure/assistant",
 *     "add-form" = "/admin/structure/assistant/add",
 *     "edit-form" = "/admin/structure/assistant/{assistant}",
 *     "delete-form" = "/admin/structure/assistant/{assistant}/delete",
 *   },
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "label",
 *     "uuid" = "uuid",
 *   },
 *   config_export = {
 *     "id",
 *     "label",
 *     "description",
 *     "model",
 *     "temperature",
 *     "topP",
 *     "system_instructions",
 *     "project_id",
 *   },
 * )
 */
final class Assistant extends ConfigEntityBase implements AssistantInterface {

  /**
   * The assistant ID.
   */
  protected string $id;

  /**
   * The assistant label.
   */
  protected string $label;

  /**
   * The assistant description.
   */
  protected string $description;

  /**
   * The model.
   */
  protected string $model;

  /**
   * The temperature.
   */
  protected float $temperature;

  /**
   * The topP.
   */
  protected float $topP;

  /**
   * The system instructions.
   */
  protected string $system_instructions;

  /**
   * The project ID.
   */
  protected string $project_id;
}