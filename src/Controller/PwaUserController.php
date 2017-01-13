<?php

/**
 * Controller to store stuff in the user object.
 */

namespace Drupal\social_pwa\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\user\Entity\User;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;


class PwaUserController extends ControllerBase {

  public function saveUser($subscription_id) {
    /** @var User $account */
    $account =  \Drupal::currentUser();

    // First fetch user data.
    $user_data = \Drupal::service('user.data')->get('social_pwa', $account->id(), 'subscription');
    if (!in_array($subscription_id, $user_data)) {
        $user_data[] = $subscription_id;

        // And save it again.
        \Drupal::service('user.data')->set('social_pwa', $account->id(), 'subscription', $user_data);
    }
//    return new Response();
    return new RedirectResponse('/');
  }

}
