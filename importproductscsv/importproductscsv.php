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

class ImportProductsCsv extends Module
{
    protected $config_form = false;

    public function __construct()
    {
        $this->name = 'importproductscsv';
        $this->tab = 'administration';
        $this->version = '1.0.0';
        $this->author = 'Earo191';
        $this->need_instance = 0;

        /**
         * Set $this->bootstrap to true if your module is compliant with bootstrap (PrestaShop 1.6)
         */
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l('ImportProductsCsv');
        $this->description = $this->l('este modulo importa productos a la tienda mediante csv ');

        $this->confirmUninstall = $this->l('');

        $this->ps_versions_compliancy = array('min' => '1.6', 'max' => _PS_VERSION_);
    }

    /**
     * Don't forget to create update methods if needed:
     * http://doc.prestashop.com/display/PS16/Enabling+the+Auto-Update
     */
    public function install()
    {
        Configuration::updateValue('IMPORTPRODUCTSCSV_LIVE_MODE', false);

        return parent::install() &&
            $this->registerHook('header') &&
            $this->registerHook('displayBackOfficeHeader');
    }

    public function uninstall()
    {
        Configuration::deleteByName('IMPORTPRODUCTSCSV_LIVE_MODE');

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
        if (((bool)Tools::isSubmit('btn_submit_csv')) == true) {
            
            $this->postProcess();
        }

        $this->context->smarty->assign('module_dir', $this->_path);

        $output = $this->context->smarty->fetch($this->local_path.'views/templates/admin/configure.tpl');

        return $output.$this->renderForm();
    }

    /**
     * Create the form that will be displayed in the configuration of your module.
     */
    protected function renderForm()
    {
        $helper = new HelperForm();

        $helper->show_toolbar = false;
        $helper->table = $this->table;
        $helper->module = $this;
        $helper->default_form_language = $this->context->language->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG', 0);

        $helper->identifier = $this->identifier;
        $helper->submit_action = 'submitImportProductsCsvModule';
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false)
            .'&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');

        $helper->tpl_vars = array(
            'fields_value' => $this->getConfigFormValues(), /* Add values for your inputs */
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id,
        );

        return $helper->generateForm(array($this->getConfigForm()));
    }

    /**
     * Create the structure of your form.
     */
    protected function getConfigForm()
    {
        return array(
            'form' => array(
                'legend' => array(
                'title' => $this->l('Settings'),
                'icon' => 'icon-cogs',
                ),
                'input' => array(
                    array(
                        'type' => 'switch',
                        'label' => $this->l('Live mode'),
                        'name' => 'IMPORTPRODUCTSCSV_LIVE_MODE',
                        'is_bool' => true,
                        'desc' => $this->l('Use this module in live mode'),
                        'values' => array(
                            array(
                                'id' => 'active_on',
                                'value' => true,
                                'label' => $this->l('Enabled')
                            ),
                            array(
                                'id' => 'active_off',
                                'value' => false,
                                'label' => $this->l('Disabled')
                            )
                        ),
                    ),
                    array(
                        'col' => 3,
                        'type' => 'text',
                        'prefix' => '<i class="icon icon-envelope"></i>',
                        'desc' => $this->l('Enter a valid email address'),
                        'name' => 'IMPORTPRODUCTSCSV_ACCOUNT_EMAIL',
                        'label' => $this->l('Email'),
                    ),
                    array(
                        'type' => 'password',
                        'name' => 'IMPORTPRODUCTSCSV_ACCOUNT_PASSWORD',
                        'label' => $this->l('Password'),
                    ),
                ),
                'submit' => array(
                    'title' => $this->l('Save'),
                ),
            ),
        );
    }

    /**
     * Set values for the inputs.
     */
    protected function getConfigFormValues()
    {
        return array(
            'IMPORTPRODUCTSCSV_LIVE_MODE' => Configuration::get('IMPORTPRODUCTSCSV_LIVE_MODE', true),
            'IMPORTPRODUCTSCSV_ACCOUNT_EMAIL' => Configuration::get('IMPORTPRODUCTSCSV_ACCOUNT_EMAIL', 'contact@prestashop.com'),
            'IMPORTPRODUCTSCSV_ACCOUNT_PASSWORD' => Configuration::get('IMPORTPRODUCTSCSV_ACCOUNT_PASSWORD', null),
        );
    }

    /**
     * Save form data.
     */
    protected function postProcess()
    {
        // die('hello');
        if (Tools::isSubmit('btn_submit_csv') == true) {
            $file_object = array();
            $file_object['name'] = $_FILES['csv_impot_product']['name'];
            $file_object['type'] = $_FILES['csv_impot_product']['type'];
            $file_object['tmp_name'] = $_FILES['csv_impot_product']['tmp_name'];
            $file_object['error'] = $_FILES['csv_impot_product']['error'];
            $file_object['size'] = $_FILES['csv_impot_product']['size'];

            error_reporting(E_ALL);
            ini_set("display_errors", 1);

            if (!((strpos($file_object['type'], "csv")) && ($file_object['size'] < 10000000))) {
                $this->context->controller->errors[] = $this->l('La extensión o el tamaño de los archivos no es correcta.
                - Se permiten archivos .gif, .jpg, .png. y de 10 mb como máximo.');
            }else{
                if (($gestor = fopen($_FILES['csv_impot_product']['tmp_name'], "r")) !== FALSE) {
                    $cont = 0;
                    while (($datos = fgetcsv($gestor, 10000, ",")) !== FALSE) {
                        $cont++;
                        if ($cont == 1) {
                            continue;
                        }
                        $name = $datos[0];
                        $reference = $datos[1];
                        $ean = $datos[2];
                        $precio_coste = $datos[3];
                        $precio_venta = $datos[4];
                        $iva = $datos[5];
                        $cantidad = $datos[6];
                        $categoria = $datos[7];
                        $marca = $datos[8];

                        $query = "SELECT id_tax FROM "._DB_PREFIX_."tax WHERE rate = ".$iva."";
                        $result = Db::getInstance()->executeS($query);
                        
                        $manufacturerProduct = $this->manufacturerAdd($marca);
                       
                        $CategorysProduct = $this->categoryAdd($categoria);



                        $sql = "SELECT id_product FROM " . _DB_PREFIX_ . "product_lang WHERE name = '${name}'";
                        $existeProduct = Db::getInstance()->executeS($sql);

                        if (!$existeProduct) {

                            $product = new Product();
                            $product->name = array((int)Configuration::get('PS_LANG_DEFAULT') => $name);
                            $product->reference = $reference;
                            $product->ean13 = $ean;
                            $product->id_manufacturer = (int)$manufacturerProduct;
                            $product->id_category_default = $CategorysProduct[0];
                            $product->wholesale_price = (float)$precio_coste;
                            $product->price = (float)$precio_venta;
                            $product->id_tax_rules_group = (int)$result[0]['id_tax'];
                            $product->add();
                            StockAvailable::setQuantity($product->id, 0, (int)$cantidad); // id_product, id_product_attribute, quantity
                            $product->addToCategories($CategorysProduct);     // After product is submitted insert all categories

    


                        } else {
                            
                            $productUpdate = new Product((int)$existeProduct[0]['id_product']);
                            $productUpdate->reference = $reference;
                            $productUpdate->ean13 = $ean;
                            $productUpdate->id_manufacturer = (int)$manufacturerProduct;
                            $productUpdate->id_category_default = $CategorysProduct[0];
                            $productUpdate->wholesale_price = (float)$precio_coste;
                            $productUpdate->price = (float)$precio_venta;
                            $productUpdate->id_tax_rules_group = (int)$result[0]['id_tax'];
                            $productUpdate->update();
                            StockAvailable::updateQuantity($productUpdate->id, 0, $cantidad, null, true);

                        }
                        
                        // die(var_dump($existeProduct));

                        // echo 'nombre: '. $name . '  reference:' . $reference . '  ean12:' .  $ean . '  PrecioC:' . $precio_coste . '  PrecioV:' . $precio_venta . '  Iva:' . $iva . '  Cantidad:' .$cantidad . '  Categorias:' .$categoria . '  Marcas:' . $marca . '<br>';
                        
                    }
                }
                
                
                $this->context->controller->confirmations[] = $this->l('Los productos se han importado correctamente :) ');
            }

        }
    }

    /**
    * Add the CSS & JavaScript files you want to be loaded in the BO.
    */
    function quitar_acentos($cadena){
        $originales = 'ÀÁÂÃÄÅÆÇÈÉÊËÌÍÎÏÐÑÒÓÔÕÖØÙÚÛÜÝÞßàáâãäåæçèéêëìíîïðñòóôõöøùúûýýþÿ';
        $modificadas = 'aaaaaaaceeeeiiiidnoooooouuuuybsaaaaaaaceeeeiiiidnoooooouuuyyby';
        $cadena = utf8_decode($cadena);
        $cadena = strtr($cadena, utf8_decode($originales), $modificadas);
        return utf8_encode($cadena);
    }

    public function categoryAdd($category){
        // die($category);
        $separador = ";";
        $categorias = explode($separador, $category);

        $CategoriaProduct[] = array();
        // die(var_dump($categorias));
        foreach ($categorias as $key => $categoria) {
     
            $sql = "SELECT id_category FROM " . _DB_PREFIX_ . "category_lang WHERE name = '${categoria}'";
            $existeCategory = Db::getInstance()->executeS($sql);
            
            if(!$existeCategory){
                // $separador = " ";
                $categorySG = strtr($categoria, " ", "-");
                $categoryComa = str_replace(",", "", $categorySG);
                $categoryNew = new Category();
                $categoryNew->name = array((int)Configuration::get('PS_LANG_DEFAULT') => $categoria);
                $categoryNew->id_shop_default = 1;
                $categoryNew->id_parent = Configuration::get('PS_HOME_CATEGORY');
                $categoryNew->link_rewrite = array((int)Configuration::get('PS_LANG_DEFAULT') => $this->quitar_acentos(($categoryComa)));
                $categoryNew->position = (int) Category::getLastPosition((int) Configuration::get('PS_HOME_CATEGORY'), 1);
                $categoryNew->add();
            
                $CategoriaProduct[$key] = (int)$categoryNew->id;


            }else{
                $CategoriaProduct[$key] = (int)$existeCategory[0]['id_category'];
            }
            
        }
        return $CategoriaProduct;

    }
    public function manufacturerAdd($marca){
        $sql = "SELECT id_manufacturer FROM " . _DB_PREFIX_ . "manufacturer WHERE name = '${marca}'";
        $existeManufacturer = Db::getInstance()->executeS($sql);
        if(!$existeManufacturer){
            $manufacturer = new Manufacturer();
            $manufacturer->name = $marca;
            $manufacturer->active = 1;
            $manufacturer->add();
            return $manufacturer->id;
        }else{
            return $existeManufacturer[0]['id_manufacturer'];

        }

    }

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
}
