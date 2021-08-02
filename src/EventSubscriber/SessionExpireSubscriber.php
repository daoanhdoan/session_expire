<?php
namespace Drupal\session_expire\EventSubscriber;

use Drupal\Component\Utility\Crypt;
use Drupal\Core\Database\Database;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;
/**
 * Redirect .html pages to corresponding Node page.
 */
class SessionExpireSubscriber implements EventSubscriberInterface {

  /** @var int */
  private $redirectCode = 301;

  /**
   * Redirect pattern based url
   * @param RequestEvent $event
   */
  public function cleanSessions(RequestEvent $event) {
    session_expire_cron();
  }

  /**
   * Listen to kernel.request events and call customRedirection.
   * {@inheritdoc}
   * @return array Event names to listen to (key) and methods to call (value)
   */
  public static function getSubscribedEvents() {
    $events[KernelEvents::REQUEST][] = array('cleanSessions');
    return $events;
  }
}
