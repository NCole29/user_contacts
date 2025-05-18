<?php

namespace Drupal\user_contacts\Plugin\Field\FieldFormatter;

use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Field\Attribute\FieldFormatter;
use Drupal\Core\Field\Plugin\Field\FieldFormatter\EntityReferenceFormatterBase;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\StringTranslation\TranslatableMarkup;

/**
 * Plugin implementation for link to the user contact form.
 */
#[FieldFormatter(
  id: "entity_reference_contact",
  label: new TranslatableMarkup("Link to contact form"),
  field_types: ["entity_reference"]
)]

class UserContactFormatter extends EntityReferenceFormatterBase {

  /**
   * {@inheritdoc}
   */
  public static function isApplicable(FieldDefinitionInterface $field_definition) {
    // ""Link to contact form" is applicable only for entity reference user fields.
    $settings = $field_definition->getItemDefinition()->getSettings();
    return ($settings['target_type'] == 'user');
  }

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $elements = [];
    $currentUserId = \Drupal::currentUser()->id();

    foreach ($this->getEntitiesToView($items, $langcode) as $delta => $entity) {

      // Entity is the user being contacted.
      $elements[$delta] = ['#entity' => $entity];

      if ($entity->getEntityTypeId() == 'user') {
        $contact_id = $entity->id();
        $contact_name = $entity->label();

        // Get URL for contact form.
        $url = \Drupal\Core\Url::fromRoute('entity.user.contact_form', ['user' => $contact_id] );

        // Is personal contact form enabled?
        $userData = \Drupal::service('user.data');
        $enabled = $userData->get('contact', $contact_id, 'enabled');

        // Display as plain text names without link if:
        //  Current user = contact person (Users may not contact themselves)
        //  Person to contact has disabled their contact form
        if ($currentUserId == $contact_id or !$enabled) {
          $elements[$delta] += [
            '#plain_text' => $contact_name,
          ];      
        } 
        else {
          $elements[$delta] += [
            '#type' => 'link',
            '#title' => $contact_name,
            '#url' => $url,
          ];      
        }
      }
    }
    return $elements;
  }
}