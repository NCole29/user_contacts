<?php

namespace Drupal\user_contacts\Routing;

use Drupal\Core\Routing\RouteSubscriberBase;
use Symfony\Component\Routing\RouteCollection;

/**
  * Class RouteSubscriber.
  *
  * @package Drupal\club_user\Routing
  */
  class RouteSubscriber extends RouteSubscriberBase {

   /**
    * {@inheritdoc}
    */
    protected function alterRoutes(RouteCollection $collection) {
      // Change path to personal contact form from '/user/{user}/contact' to '/contact/{user}/contact'.
      // Because pages with path "/user/*" display the User Account block in the left sidebar.
      if ($route = $collection->get('entity.user.contact_form')) {
        $route->setPath('/contact/{user}/contact');
      }
    }
  }
