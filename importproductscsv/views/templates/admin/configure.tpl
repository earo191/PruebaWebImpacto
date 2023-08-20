{*
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
*}

<div class="panel">
	<h3>
		<i class="icon-list-ul"></i> {l s='Importador de stock por marcas' mod='importStockVirtual'}
		
	</h3>

	<form class="defaultForm form-horizontal" id="form_prod" action="" method="post" enctype="multipart/form-data">
        
        <div class="row">
            <div class="form-group">
    			<label for="csv_import">Adjunta un archivo CSV para la importación </label>
    			<input type="file" id="csv_impot_product" name="csv_impot_product">
    			<p class="help-block">Adjunta un archivo .CSV</p>
    		</div>
        </div>
		<button type="submit" id="btn_submit_csv" name="btn_submit_csv" class="btn btn-primary">Enviar</button>
	</form>

</div>