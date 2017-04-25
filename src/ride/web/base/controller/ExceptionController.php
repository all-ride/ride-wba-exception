<?php

namespace ride\web\base\controller;

use ride\library\validation\exception\ValidationException;

use ride\service\ExceptionService;

use ride\web\base\menu\Taskbar;

use \Exception;

/**
 * Controller to ask the user's comment on the occured exception and send it
 * through email to the developers
 */
class ExceptionController extends AbstractController {

    /**
     * Action to ask for extra information and to send the error report
     * @return null
     */
    public function indexAction(ExceptionService $service, $id, $report = null) {
        $config = $this->getConfig();

        if ($report === null) {
            $report = $service->getReport($id);
        }

        $useForm = $config->get('system.exception.form', true);
        if (!$useForm) {
            $this->setTemplateView('base/exception', array(
                'id' => $id,
                'report' => $report,
                'form' => null,
            ));

            $this->response->setHeader('X-Ride-ExceptionForm', 'true');

            return;
        }

        $form = $this->createFormBuilder();
        $form->setAction('exception');
        $form->addRow('comment', 'text', array(
            'filters' => array(
                'trim' => array(),
            ),
        ));
        $form = $form->build();

        if ($form->isSubmitted()) {
            $form->validate();

            $data = $form->getData();

            $service->updateReport($id, $data['comment']);

            $routeId = $config->get('system.exception.finish.' . $this->getLocale());
            if (!$routeId) {
                $routeId = $config->get('system.exception.finish');
                if (!$routeId) {
                    $routeId = 'exception.finish';
                }
            }

            $url = $this->getUrl($routeId);

            $this->response->setRedirect($url);

            return;
        }

        $view = $this->setTemplateView('base/exception', array(
            'id' => $id,
            'report' => $report,
            'form' => $form->getView(),
        ));

        $form->processView($view);

        $this->response->setHeader('X-Ride-ExceptionForm', 'true');
    }

    /**
     * Action to show a confirmation page
     * @return null
     */
    public function finishAction() {
        $this->setTemplateView('base/exception.finish');
    }

    /**
     * Action to set the error reporting settings
     * @return null
     */
    public function settingsAction() {
        $config = $this->getConfig();
        $translator = $this->getTranslator();
        $referer = $this->getReferer();

        $recipients = $config->get('system.exception.recipient');
        if (!is_array($recipients)) {
            $recipients = array($recipients);
        }

        // build the form
        $data = array(
            'recipient' => $recipients,
            'subject' => $config->get('system.exception.subject'),
        );

        $form = $this->createFormBuilder($data);
        $form->setAction('system.exception');
        $form->addRow('recipient', 'collection', array(
            'label' => $translator->translate('label.recipient'),
            'description' => $translator->translate('label.recipient.exception.description'),
            'type' => 'email',
            'filters' => array(
                'trim' => array(),
            )
        ));
        $form->addRow('subject', 'email', array(
            'label' => $translator->translate('label.subject'),
            'description' => $translator->translate('label.subject.exception.description'),
            'filters' => array(
                'trim' => array(),
            )
        ));
        $form = $form->build();

        // handle the form
        if ($form->isSubmitted()) {
            try {
                $form->validate();

                $data = $form->getData();

                $config->set('system.exception.recipient', $data['recipient'] == '' ? null : $data['recipient']);
                $config->set('system.exception.subject', $data['subject'] == '' ? null : $data['subject']);

                $this->addSuccess('success.preferences.saved');

                if (!$referer) {
                    $referer = $this->getUrl('system.exception');
                }

                $this->response->setRedirect($referer);

                return;
            } catch (ValidationException $exception) {
                $this->setValidationException($exception, $form);
            }
        }

        $this->setTemplateView('base/settings/exception', array(
            'form' => $form->getView(),
            'referer' => $referer,
        ));
    }

}
