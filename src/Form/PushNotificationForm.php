<?php

namespace Drupal\social_pwa\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\user\Entity\User;
use Minishlink\WebPush\WebPush;

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
      '#title' => $this->t('Send a Push Notification'),
      '#open' => FALSE,
    );

    // Retrieve all uid
    $user_query = \Drupal::entityQuery('user');
    $user_query->condition('uid',0,'>');
    $user_list = $user_query->execute();

    // Filter to check which users have subscription?
    foreach ($user_list as $key => &$value) {
      /** @var User $account */
      if ($account = User::load($key)) {
        $user_subscription = \Drupal::service('user.data')->get('social_pwa', $account->id(), 'subscription');
        if (isset($user_subscription)) {
          $value = $account->getDisplayName() . ' (' . $account->getAccountName() . ')';
          continue;
        }
        unset($user_list[$key]);
      }
    }

    $form['push_notification']['selected-user'] = array(
      '#type' => 'select',
      '#title' => $this->t('To user'),
      '#description' => $this->t('This is a list of users that have given permission to receive notifications.'),
      '#options' => $user_list, // -> then provide filtered list
    );

    $form['push_notification']['title'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Title'),
      '#size' => 30,
      '#maxlength' => 25,
      '#default_value' => 'Not working yet..',
      '#description' => $this->t('This will be the <b>title</b> of the Push Notification.'),
    );
    $form['push_notification']['message'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Message'),
      '#size' => 90,
      '#maxlength' => 120,
      '#default_value' => 'This is not working yet..',
      '#description' => $this->t('This will be the <b>message</b> of the Push Notification.'),
    );

    // TODO: Maybe create a fieldset where the user fills in an url for redirect when the user clicks the notification.

    $form['actions']['#type'] = 'actions';
    $form['actions']['submit'] = array(
      '#type' => 'submit',
      '#value' => $this->t('Send Push Notification'),
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

    //TODO: Waar wordt dit formulier naar toe verstuurd? Fetch moet hierop luisteren dmv callback?

    // The selected uid value of the form
    $uid = $form_state->getValue('selected-user');
    if (!empty($uid)) {
      // Get subscription value of the selected user
      $user_subscription = \Drupal::service('user.data')->get('social_pwa', $uid, 'subscription');
      $endpoint = $user_subscription[0];

      $title = $form_state->getValue('title');
      $message = $form_state->getValue('message');

      $payload = json_encode(array($title, $message));

      // array of notifications
      $notifications = array(
        array(
          'endpoint' => $endpoint,
          'payload' => $payload,
          'userPublicKey' => null, //'BFhe5EFfcPn0XDnBAgNGPIqKocwI-yimiWet1fQXNbFtCwlRzmGVDTJoG8fjxjXEXmFqt8BzcaDtkFyTdUk2cb8',
          'userAuthToken' => null, //'4iyfc5VbYDifpZ9170MY-xDXVjEmg3tOKRriFFl4Wxo',
        )
      );

      $auth = array(
        //'GCM' => 'MY_GCM_API_KEY', // Deprecated and optional, it's here only for compatibility reasons
        'VAPID' => array(
          'subject' => 'mailto:frankgraave@gmail.com', // Can be a mailto: or your website address
          'publicKey' => 'BFhe5EFfcPn0XDnBAgNGPIqKocwI-yimiWet1fQXNbFtCwlRzmGVDTJoG8fjxjXEXmFqt8BzcaDtkFyTdUk2cb8', // (recommended) uncompressed public key P-256 encoded in Base64-URL
          'privateKey' => '4iyfc5VbYDifpZ9170MY-xDXVjEmg3tOKRriFFl4Wxo', // (recommended) in fact the secret multiplier of the private key encoded in Base64-URL
        ),
      );

      $webPush = new WebPush($auth);

      foreach ($notifications as $notification) {
        $webPush->sendNotification(
          $notification['endpoint'],
          $notification['payload'],
          $notification['userPublicKey'],
          $notification['userAuthToken']
        );
      }

      $webPush->flush();

    }
    drupal_set_message($this->t('Messages were succesfully sent!'));
  }

}