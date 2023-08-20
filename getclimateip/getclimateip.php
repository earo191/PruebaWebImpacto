<?php
/**
* 2007-2023 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
*  @author    PrestaShop SA <contact@prestashop.com>
*  @copyright 2007-2023 PrestaShop SA
*  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/

if (!defined('_PS_VERSION_')) {
    exit;
}

class GetClimateIP extends Module
{
    protected $config_form = false;

    public function __construct()
    {
        $this->name = 'getclimateip';
        $this->tab = 'analytics_stats';
        $this->version = '1.0.0';
        $this->author = 'Earo191';
        $this->need_instance = 0;

        /**
         * Set $this->bootstrap to true if your module is compliant with bootstrap (PrestaShop 1.6)
         */
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l('Clima Address');
        $this->description = $this->l('muestra el clima en la ciudad del cliente');

        $this->confirmUninstall = $this->l('');

        $this->ps_versions_compliancy = array('min' => '1.6', 'max' => _PS_VERSION_);
    }

    /**
     * Don't forget to create update methods if needed:
     * http://doc.prestashop.com/display/PS16/Enabling+the+Auto-Update
     */
    public function install()
    {
        Configuration::updateValue('GETCLIMATEIP_LIVE_MODE', false);

        return parent::install() &&
            $this->registerHook('header') &&
            $this->registerHook('displayBackOfficeHeader') &&
            $this->registerHook('displayBackOfficeHeader') &&
            $this->registerHook('displayNav2');
    }

    public function uninstall()
    {
        Configuration::deleteByName('GETCLIMATEIP_LIVE_MODE');

        return parent::uninstall();
    }

    /**
     * Load the configuration form
     */
    public function getContent()
    {
        /**
         * If values have been submitted in the form, process.
         */
        if (((bool)Tools::isSubmit('submitGetClimateIPModule')) == true) {
            $this->postProcess();
        }
        $this->context->smarty->assign(['api' => Configuration::get('api')]);
           

        $output = $this->context->smarty->fetch($this->local_path.'views/templates/admin/configure.tpl');

        return $output;
    }

    

    /**
     * Set values for the inputs.

    /**
     * Save form data.
     */
    protected function postProcess()
    {
        // die(Tools::getValue('api'));
        Configuration::updateValue('api', Tools::getValue('api'));
        $this->context->controller->confirmations[] = $this->l('Api key Guardada');
    }

    /**
    * Add the CSS & JavaScript files you want to be loaded in the BO.
    */
    public function hookDisplayBackOfficeHeader()
    {
        if (Tools::getValue('configure') == $this->name) {
            $this->context->controller->addJS($this->_path.'views/js/back.js');
            $this->context->controller->addCSS($this->_path.'views/css/back.css');
        }
    }

    /**
     * Add the CSS & JavaScript files you want to be added on the FO.
     */
    public function hookHeader()
    {
        $this->context->controller->addJS($this->_path.'/views/js/front.js');
        $this->context->controller->addCSS($this->_path.'/views/css/front.css');
    }

    public function hookDisplayNav2()
    {
        
        $ip = file_get_contents('https://api.ipify.org');
        $apiKey = Configuration::get('api');
        $endpoint = 'https://api.weatherapi.com/v1/current.json';
        $query = 'q='.$ip;

        $url = $endpoint . '?' . $query . '&key=' . $apiKey;
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($curl);
        curl_close($curl);

        $data = json_decode($response, true);

        $ciudad = $data['location']['name'];
        $pais = $data['location']['country'];
        $temperatura = $data['current']['temp_c'];
        $dia = $data['current']['condition']['icon'];
        $humedad = $data['current']['humidity'];
        $precipitacion = $data['current']['precip_mm'];

        $this->context->smarty->assign(['module_dir', $this->_path, 'ciudad' =>$ciudad, 'pais' =>$pais, 'temperatura' => $temperatura, 'dia' => $dia, 'humedad'=> $humedad, 'precipitacion'=>$precipitacion]);
        return $this->display(__FILE__, '/views/templates/hook/displayNavFullWidth.tpl');
    }
}
