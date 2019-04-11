<?php 
// src/EventSubscriber/LocaleSubscriber.php
namespace App\EventSubscriber;

use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class LocaleSubscriber implements EventSubscriberInterface
{
    private $defaultLocale;

    public function __construct($defaultLocale = 'en')
    {
        $this->defaultLocale = $defaultLocale;
    }

    public function onKernelRequest(GetResponseEvent $event)
    {
        $request = $event->getRequest();
        /*if (!$request->hasPreviousSession()) {
            return;
        }*/
        
        if (explode('/', $request->getPathInfo())[1] != "live") {
            $request->getSession()->set('_locale', $request->getPreferredLanguage(array('en', 'fr')));
        }
        
        // try to see if the locale has been set as a _locale routing parameter
        if ($locale = $request->attributes->get('_locale')) {
            $request->getSession()->set('_locale', $locale);
        } else {
            // if no explicit locale has been set on this request, use one from the session
            $request->setLocale($request->getSession()->get('_locale', $this->defaultLocale));
        }
        
        if ($request->get('_route') && $request->get('_route') != "oauth_authorize" && $request->get('_route') != "_wdt" && $request->get('_route') != "oauth_logout" && substr($request->get('_route'), 0, 5) != "ajax_") {
            $request->getSession()->set('lastURI', $request->getUri());
        }
    }

    public static function getSubscribedEvents()
    {
        return array(
            // must be registered before (i.e. with a higher priority than) the default Locale listener
            KernelEvents::REQUEST => array(array('onKernelRequest', 20)),
        );
    }
}