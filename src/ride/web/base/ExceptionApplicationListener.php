<?php

namespace ride\web\base;

use ride\library\event\Event;
use ride\library\event\EventManager;
use ride\library\http\Header;
use ride\library\i18n\I18n;
use ride\library\security\exception\UnauthorizedException;
use ride\library\security\SecurityManager;

use ride\web\base\service\ExceptionService;
use ride\web\WebApplication;

/**
 * Application listener to override the default exception view with a error
 * reporting action
 */
class ExceptionApplicationListener {

    /**
     * Handle a exception, redirect to the error report form
     * @param \ride\library\event\Event $event
     * @param \ride\library\security\SecurityManager $securityManager
     * @return null
     */
    public function handleException(Event $event, ExceptionService $service, SecurityManager $securityManager, EventManager $eventManager, I18n $i18n) {
        $exception = $event->getArgument('exception');
        if ($exception instanceof UnauthorizedException) {
            return;
        }

        // gather needed variables
        $web = $event->getArgument('web');
        $request = $web->getRequest();
        $response = $web->getResponse();
        $locale = $i18n->getLocale();
        $user = $securityManager->getUser();

        // write report
        $report = $service->createReport($exception, $request, $user);
        $id = $service->writeReport($report);

        // dispatch to the exception route
        $route = $web->getRouterService()->getRouteById('exception.' . $locale->getCode());
        if ($route === null) {
            $route = $web->getRouterService()->getRouteById('exception', array('id' => $id));

            $route->setArguments(array('id' => $id));
            $route->setPredefinedArguments(array('report' => $report));
        }

        $request = $web->createRequest($route->getPath(), 'GET');
        $request->setRoute($route);

        $response->setView(null);
        $response->removeHeader(Header::HEADER_CONTENT_TYPE);
        $response->clearRedirect();

        $dispatcher = $web->getDispatcher();
        $dispatcher->dispatch($request, $response);

        if ($web->getState() == WebApplication::STATE_RESPONSE) {
            // exception occured while rendering the template, trigger the pre
            // response event again for the new view
            $eventManager->triggerEvent(WebApplication::EVENT_PRE_RESPONSE, array('web' => $web));
        }
    }

}
