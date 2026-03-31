<?php

if (!defined('_PS_VERSION_')) {
    exit;
}

class Geoip_UpdaterUpdateModuleFrontController extends ModuleFrontController
{
    public $display_header = false;
    public $display_footer = false;
    public $ssl = true;

    public function initContent()
    {
        parent::initContent();

        header('Content-Type: application/json; charset=utf-8');

        $token = Tools::getValue('token');
        $storedToken = Configuration::get('GEOIP_UPDATER_TOKEN');
        if (empty($token) || $token !== $storedToken) {
            echo json_encode([
                'success' => false,
                'message' => $this->module->l('Invalid token.'),
            ]);
            exit;
        }

        $result = $this->module->runUpdate();
        echo json_encode([
            'success' => $result['success'],
            'message' => $result['message'],
        ]);
        exit;
    }
}
