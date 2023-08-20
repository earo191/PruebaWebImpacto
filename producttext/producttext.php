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

class ProductText extends Module
{
    protected $config_form = false;

    public function __construct()
    {
        $this->name = 'producttext';
        $this->tab = 'administration';
        $this->version = '1.0.0';
        $this->author = 'Earo191';
        $this->need_instance = 0;

        /**
         * Set $this->bootstrap to true if your module is compliant with bootstrap (PrestaShop 1.6)
         */
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l('Product Text');
        $this->description = $this->l('texto personalizado al producto ');

        $this->confirmUninstall = $this->l('');

        $this->ps_versions_compliancy = array('min' => '1.6', 'max' => _PS_VERSION_);
    }

    /**
     * Don't forget to create update methods if needed:
     * http://doc.prestashop.com/display/PS16/Enabling+the+Auto-Update
     */
    public function install()
    {
        Configuration::updateValue('PRODUCTTEXT_LIVE_MODE', false);

        include(dirname(__FILE__).'/sql/install.php');

        return parent::install() &&
            $this->registerHook('header') &&
            $this->registerHook('displayBackOfficeHeader') &&
            $this->registerHook('actionProductUpdate') &&
            $this->registerHook('displayTextProduct') &&
            $this->registerHook('displayAdminProductsMainStepLeftColumnMiddle');
    }

    public function uninstall()
    {
        Configuration::deleteByName('PRODUCTTEXT_LIVE_MODE');

        include(dirname(__FILE__).'/sql/uninstall.php');

        return parent::uninstall();
    }


    protected function getConfigFormValues()
    {
        return array(
            'PRODUCTTEXT_LIVE_MODE' => Configuration::get('PRODUCTTEXT_LIVE_MODE', true),
            'PRODUCTTEXT_ACCOUNT_EMAIL' => Configuration::get('PRODUCTTEXT_ACCOUNT_EMAIL', 'contact@prestashop.com'),
            'PRODUCTTEXT_ACCOUNT_PASSWORD' => Configuration::get('PRODUCTTEXT_ACCOUNT_PASSWORD', null),
        );
    }

    /**
     * Save form data.
     */
    protected function postProcess()
    {
        $form_values = $this->getConfigFormValues();

        foreach (array_keys($form_values) as $key) {
            Configuration::updateValue($key, Tools::getValue($key));
        }
    }

    /**
    * Add the CSS & JavaScript files you want to be loaded in the BO.
    */
    public function hookDisplayBackOfficeHeader()
    {
            $this->context->controller->addJquery();
            $this->context->controller->addJS($this->_path.'views/js/back.js');
            $this->context->controller->addCSS($this->_path.'views/css/back.css');
        
    }

    /**
     * Add the CSS & JavaScript files you want to be added on the FO.
     */
    public function hookHeader()
    {
        $this->context->controller->addJS($this->_path.'/views/js/front.js');
        $this->context->controller->addCSS($this->_path.'/views/css/front.css');
    }

    public function hookDisplayAdminProductsMainStepLeftColumnMiddle($params)
    {
        /* Place your code here. */
        $id_product = $params['id_product'];
        $texExist = Db::getInstance()->executeS("SELECT producttext FROM " . _DB_PREFIX_ . "producttext WHERE id_producto = " . (int)$id_product);
        $this->context->smarty->assign(['producttext' => $texExist[0]['producttext']]);
        $output = $this->context->smarty->fetch($this->local_path . 'views/templates/admin/hook/textProduct.tpl');
        return $output;
    }

    public function hookActionProductUpdate($params)
    {
        $text=  Tools::getValue('text_personalizado');
        $id_product = $params['id_product'];
        $sql;
        $texExist = Db::getInstance()->executeS("SELECT producttext FROM " . _DB_PREFIX_ . "producttext WHERE id_producto = " . (int)$id_product);
        if(!$texExist){
            $sql = "INSERT INTO " . _DB_PREFIX_ . "producttext (producttext,id_producto) VALUES ('" . (string)$text ."'," . (int)$id_product . ")" ;
            // die($sql);
            $result = Db::getInstance()->executeS($sql);
        }else{
            if($texExist[0]['producttext'] != $text){
                $sql = "UPDATE " . _DB_PREFIX_ . "producttext SET producttext = '${text}' where id_producto='${id_product}' ";
                $result = Db::getInstance()->executeS($sql);
            }
            
        }

    }
    public function hookDisplayTextProduct($params){
        $id_product = $params['product']["id_product"];
        $texExist = Db::getInstance()->executeS("SELECT producttext FROM " . _DB_PREFIX_ . "producttext WHERE id_producto = " . (int)$id_product);
        $this->context->smarty->assign(['producttext' => $texExist[0]['producttext']]);
        return $this->display(__FILE__, '/views/templates/front/hook/displayTextProduct.tpl');
    }
}
