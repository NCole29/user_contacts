<?php

namespace Drupal\user_contactlink\Plugin\Field\FieldFormatter;

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
    // "Link to contact form" is an option only for entity reference user fields.
    $settings = $field_definition->getItemDefinition()->getSettings();
    return ($settings['target_type'] == 'user');
  }

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $elements = [];
    $currentUserId = \Drupal::currentUser()->id();

    $contacts = $items->getValue('target_id'); // Array contact persons.

    foreach($contacts as $delta => $contact) {
        
        $uid = $contact['target_id'];
        $contact = \Drupal::service('entity_type.manager')->getStorage('user')->load($uid);

        // Contact is the user entity being contacted.
        $elements[$delta] = ['#entity' => $contact];

        $contact_id = $contact->id();
        $contact_name = $contact->label();

        // Get URL for contact form.
        $url = \Drupal\Core\Url::fromRoute('entity.user_contactlink.contact_form', ['user' => $contact_id] );
                                         
        // Is personal contact form enabled for person being contacted?
        $userData = \Drupal::service('user.data');
        $enabled = $userData->get('contact', $contact_id, 'enabled');

        // Plain text if $contact did not enable contact form, and so user cannot contact self.
        if ( !$enabled or $currentUserId == $contact_id ) {
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
    return $elements;
  }
}