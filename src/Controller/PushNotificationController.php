<?php

namespace Drupal\social_pwa\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;

class PushNotificationController extends ControllerBase {

  /**
   * @param Request $request
   * @param $endpoint
   *
   * @return JsonResponse
   */
  public function extractData(Request $request, $endpoint) {

    // need some magic to get the message/payload here

    return new JsonResponse(array('notification' => array('message' => 'Hard test')));

  }

}