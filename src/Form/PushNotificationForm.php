<?php

namespace Drupal\social_pwa\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

class PushNotificationForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'push_notification_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    // Start the form for sending push notifications
    $form['push_notification'] = array(
      '#type' => 'fieldset',
      '#title' => $this->t('Extreme Push Notification Generator 2000'),
      '#open' => FALSE,
    );
    $form['push_notification']['title'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Title'),
      '#size' => 30,
      '#maxlength' => 25,
      '#description' => $this->t('This will be the <b>title</b> of the Push Notification.'),
    );
    $form['push_notification']['message'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Message'),
      '#size' => 90,
      '#maxlength' => 120,
      '#description' => $this->t('This will be the <b>message</b> of the Push Notification.'),
    );

    // TODO: Maybe create a fieldset where the user fills in an url for redirect when the user clicks the notification.

    $form['actions']['#type'] = 'actions';
    $form['actions']['submit'] = array(
      '#type' => 'submit',
      '#value' => $this->t('Save'),
      '#button_type' => 'primary',
    );
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    parent::validateForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // Do the API call to Google Cloud Messaging / Firebase.
    // Return with drupal message that the message is successfully send.
  }

}