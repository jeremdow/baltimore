<?php

/**
 * Controller to store stuff in the user object.
 */

namespace Drupal\social_pwa\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\user\Entity\User;
use Symfony\Component\HttpFoundation\Response;

//TODO: Rename this to something more logic like "SubscriptionController"

class PwaUserController extends ControllerBase {

  public function saveUser() {
    /** @var User $account */
    $account =  \Drupal::currentUser();

    // Get the subscription object in which we obtain the endpoint
    $subscriptionData = json_decode(\Drupal::request()->getContent(), TRUE);

    // First fetch user data.
    $user_data = \Drupal::service('user.data')->get('social_pwa', $account->id(), 'subscription');

    // Check if there already is an endpoint for this user
    if (!in_array($subscriptionData['endpoint'], $user_data)) {
        $user_data[] = $subscriptionData['endpoint'];

        // And save it again.
        \Drupal::service('user.data')->set('social_pwa', $account->id(), 'subscription', $user_data);
    }
    return new Response();
  }

}
