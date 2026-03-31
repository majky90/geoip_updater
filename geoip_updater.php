<?php

if (!defined('_PS_VERSION_')) {
    exit;
}

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

class geoip_updater extends Module
{
    public function __construct()
    {
        $this->name = 'geoip_updater';
        $this->tab = 'administration';
        $this->version = '1.0.2';
        $this->author = 'Marián Varga - webvision.sk';
        $this->need_instance = 0;
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l('GeoLite2 Database Updater');
        $this->description = $this->l('Downloads and updates the GeoLite2 City database automatically.');
        $this->ps_versions_compliancy = [
            'min' => '1.7.0.0',
            'max' => '9.9.9',
        ];
    }

    public function install()
    {
        if (!parent::install()) {
            return false;
        }

        $token = bin2hex(random_bytes(8));
        if (!Configuration::updateValue('GEOIP_UPDATER_TOKEN', $token)) {
            return false;
        }

        if (!Configuration::updateValue('GEOIP_UPDATER_LAST_UPDATE', '')) {
            return false;
        }

        return $this->ensureGeoipDirectory();
    }

    public function upgrade_module_1_0_1()
    {
        if (!$this->ensureGeoipDirectory()) {
            return false;
        }

        if (!Configuration::get('GEOIP_UPDATER_TOKEN')) {
            Configuration::updateValue('GEOIP_UPDATER_TOKEN', bin2hex(random_bytes(8)));
        }

        if (!Configuration::get('GEOIP_UPDATER_LAST_UPDATE')) {
            Configuration::updateValue('GEOIP_UPDATER_LAST_UPDATE', '');
        }

        return true;
    }

    public function uninstall()
    {
        Configuration::deleteByName('GEOIP_UPDATER_TOKEN');
        Configuration::deleteByName('GEOIP_UPDATER_LAST_UPDATE');

        return parent::uninstall();
    }

    protected function ensureGeoipDirectory(): bool
    {
        $directory = _PS_ROOT_DIR_ . '/app/Resources/geoip/';

        if (!is_dir($directory)) {
            if (!mkdir($directory, 0755, true) && !is_dir($directory)) {
                PrestaShopLogger::addLog($this->l('Unable to create GeoIP storage directory.'), 3, null, 'Module', $this->id, true);
                return false;
            }
        }

        return true;
    }

    protected function getServerTimezone(): string
    {
        $timezone = Configuration::get('PS_TIMEZONE');
        if ($timezone) {
            try {
                new DateTimeZone($timezone);
                return $timezone;
            } catch (Exception $e) {
                // fall back to PHP default timezone
            }
        }

        return date_default_timezone_get();
    }

    public function getContent()
    {
        $output = '';

        if (Tools::isSubmit('submit_manual_update')) {
            $result = $this->runUpdate();
            if ($result['success']) {
                $output .= $this->displayConfirmation($result['message']);
            } else {
                $output .= $this->displayError($result['message']);
            }
        }

        $token = Configuration::get('GEOIP_UPDATER_TOKEN');
        $cronUrl = $this->context->link->getModuleLink($this->name, 'update', [], true);
        if (!empty($token)) {
            $cronUrl .= (strpos($cronUrl, '?') === false ? '?' : '&') . 'token=' . $token;
        }

        $mmdbPath = _PS_ROOT_DIR_ . '/app/Resources/geoip/GeoLite2-City.mmdb';
        $fileExists = file_exists($mmdbPath);
        $lastUpdate = Configuration::get('GEOIP_UPDATER_LAST_UPDATE');
        $serverTimezone = date_default_timezone_get();

        if ($lastUpdate) {
            $date = DateTime::createFromFormat(
                'Y-m-d H:i:s',
                $lastUpdate,
                new DateTimeZone($serverTimezone)
            );
            if ($date) {
                $lastUpdate = $date->format('Y-m-d H:i:s');
            } else {
                $lastUpdate = $this->l('Never');
                $serverTimezone = '';
            }
        } else {
            $lastUpdate = $this->l('Never');
            $serverTimezone = '';
        }

        $this->context->smarty->assign([
            'file_exists' => $fileExists,
            'last_update' => $lastUpdate,
            'cron_url' => $cronUrl,
            'token' => $token,
            'mmdb_path' => $mmdbPath,
            'server_timezone' => $serverTimezone,
            'module' => $this,
        ]);

        return $output . $this->display(__FILE__, 'views/templates/admin/configure.tpl');
    }

    public function runUpdate(): array
    {
        $directory = _PS_ROOT_DIR_ . '/app/Resources/geoip/';
        if (!$this->ensureGeoipDirectory()) {
            $message = $this->l('Unable to ensure the GeoIP directory exists.');
            PrestaShopLogger::addLog($message, 3, null, 'Module', $this->id, true);
            return ['success' => false, 'message' => $message];
        }

        $downloadUrl = 'https://cdn.jsdelivr.net/npm/geolite2-city/GeoLite2-City.mmdb.gz';
        $tmpGz = $directory . 'GeoLite2-City.mmdb.gz.tmp';
        $tmpMmdb = $directory . 'GeoLite2-City.mmdb.tmp';
        $finalMmdb = $directory . 'GeoLite2-City.mmdb';

        try {
            $client = new Client([
                'timeout' => 120,
                'verify' => true,
                'headers' => [
                    'User-Agent' => 'PrestaShop geoip_updater',
                ],
            ]);

            $response = $client->get($downloadUrl, ['sink' => $tmpGz]);
            if ($response->getStatusCode() !== 200) {
                throw new \Exception($this->l('Download returned status code ') . $response->getStatusCode());
            }
        } catch (GuzzleException | \Exception $e) {
            @unlink($tmpGz);
            PrestaShopLogger::addLog($e->getMessage(), 3, null, 'Module', $this->id, true);
            return ['success' => false, 'message' => $this->l('Download failed: ') . $e->getMessage()];
        }

        if (!file_exists($tmpGz) || filesize($tmpGz) === 0) {
            @unlink($tmpGz);
            $message = $this->l('The downloaded archive is empty.');
            PrestaShopLogger::addLog($message, 3, null, 'Module', $this->id, true);
            return ['success' => false, 'message' => $message];
        }

        try {
            $gz = gzopen($tmpGz, 'rb');
            if ($gz === false) {
                throw new \Exception($this->l('Unable to open the downloaded archive.'));
            }

            $out = fopen($tmpMmdb, 'wb');
            if ($out === false) {
                gzclose($gz);
                throw new \Exception($this->l('Unable to create temporary database file.'));
            }

            while (!gzeof($gz)) {
                $data = gzread($gz, 4096);
                if ($data === false) {
                    fclose($out);
                    gzclose($gz);
                    throw new \Exception($this->l('Unable to read from the compressed archive.'));
                }
                fwrite($out, $data);
            }

            gzclose($gz);
            fclose($out);
        } catch (\Exception $e) {
            @unlink($tmpGz);
            @unlink($tmpMmdb);
            PrestaShopLogger::addLog($e->getMessage(), 3, null, 'Module', $this->id, true);
            return ['success' => false, 'message' => $this->l('Unable to extract the database file: ') . $e->getMessage()];
        }

        if (!file_exists($tmpMmdb) || filesize($tmpMmdb) < 10 * 1024 * 1024) {
            @unlink($tmpGz);
            @unlink($tmpMmdb);
            $message = $this->l('Extracted database file is too small or invalid.');
            PrestaShopLogger::addLog($message, 3, null, 'Module', $this->id, true);
            return ['success' => false, 'message' => $message];
        }

        if (file_exists($finalMmdb) && !@unlink($finalMmdb)) {
            @unlink($tmpGz);
            @unlink($tmpMmdb);
            $message = $this->l('Unable to replace the existing database file.');
            PrestaShopLogger::addLog($message, 3, null, 'Module', $this->id, true);
            return ['success' => false, 'message' => $message];
        }

        if (!@rename($tmpMmdb, $finalMmdb)) {
            @unlink($tmpGz);
            @unlink($tmpMmdb);
            $message = $this->l('Unable to move the database file into place.');
            PrestaShopLogger::addLog($message, 3, null, 'Module', $this->id, true);
            return ['success' => false, 'message' => $message];
        }

        @unlink($tmpGz);
        Configuration::updateValue('GEOIP_UPDATER_LAST_UPDATE', date('Y-m-d H:i:s'));

        $successMessage = $this->l('GeoLite2 database has been updated successfully.');
        PrestaShopLogger::addLog($successMessage, 1, null, 'Module', $this->id, true);

        return ['success' => true, 'message' => $successMessage];
    }
}
