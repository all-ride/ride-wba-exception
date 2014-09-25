<?php

namespace ride\web\base\controller;

use ride\library\mail\transport\Transport;
use ride\library\system\System;

use ride\web\base\menu\Taskbar;
use ride\web\base\service\ExceptionService;

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
    public function indexAction(System $system, ExceptionService $service, Transport $transport, $id, $report = null) {
        if ($report === null) {
            $report = $service->getReport($id);
        }

        $form = $this->createFormBuilder();
        $form->addRow('comment', 'text', array(
            'filters' => array(
                'trim' => array(),
            ),
        ));
        $form = $form->build();

        if ($form->isSubmitted()) {
            $form->validate();

            $data = $form->getData();

            if ($data['comment']) {
                $service->updateReport($id, $data['comment']);

                $body = "User's comment: " . $data['comment'] . "\n\n" . $report;
            } else {
                $body = $report;
            }

            $recipient = $service->getRecipient();
            if ($recipient) {
                $mail = $transport->createMessage();
                $mail->setTo($recipient);
                $mail->setSubject($service->getSubject());
                $mail->setMessage($report);

                $transport->send($mail);
            }

            $this->addSuccess('success.exception.report.sent');

            $this->response->setRedirect($this->request->getBaseUrl());

            return;
        }

        $view = $this->setTemplateView('base/exception', array(
            'id' => $id,
            'report' => $report,
            'form' => $form->getView(),
        ));

        $form->processView($view);
    }

}