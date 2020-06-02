<?php

use PleskExt\GrafanaIntegrationExample\DashboardSettings;

class IndexController extends \pm_Controller_Action
{

    public function indexAction()
    {
        $this->view->settings = DashboardSettings::getAllValues();
    }

    /**
     * @throws \Exception
     */
    public function updateSettingsAction()
    {
        if (!$this->_request->isPost()) {
            throw new \Exception('Bad request');
        }

        $newSettings = $this->getParam('settings');
        DashboardSettings::setAllValues($newSettings);

        \pm_ApiCli::call('extension', ['--call', 'grafana', '--update-module-state', \pm_Context::getModuleId()]);

        $this->_helper->json->sendJson([]);
    }
}
