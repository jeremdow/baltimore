<?php

namespace Drupal\social_pwa\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class PushNotificationController extends ControllerBase {

  /**
   * @param $endpoint
   *
   * @return JsonResponse
   */
  public function sendPush(Response $response, $endpoint) {

    // Get the subscription object in which we obtain the endpoint
    $response = json_decode(\Drupal::request()->getContent(), TRUE);

    return new JsonResponse(array('notification' => array('message' => $response)));
  }

}