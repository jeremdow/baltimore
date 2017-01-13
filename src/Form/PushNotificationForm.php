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

    // TODO: Get user $account -> $subscription_id (maybe dropdown?)

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

//    // The selected uid value of the form
//    $uid = $form_state->getValue('selected-user');
//
//    // Get subscription value of the selected user
//    $user_subscription = \Drupal::service('user.data')->get('social_pwa', $uid, 'subscription');
//
//    // Not necessary, only here to show the subscription id.
//    $form['push_notification']['to'] = array(
//      '#type' => 'textfield',
//      '#title' => $this->t('The user has the following subscription ID:'),
//      '#size' => 140,
//      '#disabled' => TRUE,
//      '#default_value' => $user_subscription[1], // Print the subscription value of the selected uid.
//    );

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
  public function submitForm(array &$form, FormStateInterface $form_state)
  {

    // The selected uid value of the form
    $uid = $form_state->getValue('selected-user');
    if (!empty($uid)) {
      // Get subscription value of the selected user
      $user_subscription = \Drupal::service('user.data')->get('social_pwa', $uid, 'subscription');

      $endpoint = $user_subscription[0];

      //TODO: lege array maken en dan de value checken uit de formstate -> array.

      // array of notifications
      $notifications = array(
        array(
          'endpoint' => 'https://updates.push.services.mozilla.com/wpush/v2/' . $endpoint, // Firefox 43+
          'payload' => 'hello !',
          'userPublicKey' => 'BFhe5EFfcPn0XDnBAgNGPIqKocwI-yimiWet1fQXNbFtCwlRzmGVDTJoG8fjxjXEXmFqt8BzcaDtkFyTdUk2cb8',
          'userAuthToken' => '4iyfc5VbYDifpZ9170MY-xDXVjEmg3tOKRriFFl4Wxo',
        ), array(
          'endpoint' => 'https://fcm.googleapis.com/fcm/send/' . $endpoint, // Chrome 
          'payload' => 'Hallo',
          'userPublicKey' => null,
          'userAuthToken' => null,
        ),
      );

      $auth = array(
        //'GCM' => 'MY_GCM_API_KEY', // deprecated and optional, it's here only for compatibility reasons
        'VAPID' => array(
          'subject' => 'mailto:frankgraave@gmail.com', // can be a mailto: or your website address
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
//    // The selected uid value of the form
//    $uid = $form_state->getValue('selected-user');
//    // Get subscription value of the selected user
//    $user_subscription = \Drupal::service('user.data')->get('social_pwa', $uid, 'subscription');
//
//    $token = $user_subscription;
//    $title = $form_state->getValue('title');
//    $message = $form_state->getValue('message');
//
//    $fields = array (
//      'registration_ids' => $token,
//      'data' => array(
//        'title' => $title,
//        'message' => $message
//      )
//    );
//
//    $headers = array (
//      'Authorization: key=AIzaSyCzoz6AfHfEbaN_7ysmidCcFmKVQQPIG7w',
//      'Content-Type: application/json'
//    );
//
//    $ch = curl_init ();
//    curl_setopt ( $ch, CURLOPT_URL, 'https://fcm.googleapis.com/fcm/send' );
//    curl_setopt ( $ch, CURLOPT_POST, true );
//    curl_setopt ( $ch, CURLOPT_HTTPHEADER, $headers );
//    curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, true );
//    curl_setopt ( $ch, CURLOPT_POSTFIELDS, json_encode($fields) );
//
//    $result = curl_exec ( $ch );
//    echo $result;
//    curl_close ( $ch );
//  }

}