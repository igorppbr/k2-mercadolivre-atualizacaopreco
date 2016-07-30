<?php
/**
 * Created by PhpStorm.
 * User: igorludgeromiura
 * Date: 12/07/16
 * Time: 23:46
 */

class Igorludgero_Atualizapreco_Adminhtml_Catalog_ProductController extends Mage_Adminhtml_Controller_Action
{


    public function updatebundlesAction()
    {
        try {
            $model = Mage::getModel('catalog/product');
            $helper = Mage::helper('atualizapreco');
            $productIds = $this->getRequest()->getParam('product');
            $contador = 0;
            if (count($productIds) > 0) {
                foreach ($productIds as $productId) {
                    if($helper->updatebundles($productId)) {
                        $contador++;
                    }
                }
                Mage::getSingleton('core/session')->addSuccess("Os bundles pertencentes a " . $contador . " produtos foram atualizados.");
            } else {
                Mage::getSingleton('core/session')->addError("Selecione pelo menos um produto.");
            }
            $this->_redirect('adminhtml/catalog_product/index/', array());
        }
        catch(Exception $ex){
            Mage::log("erro ao dar update nos bundles do simples: ".$ex->getMessage());
            $this->_redirect('adminhtml/catalog_product/index/', array());
        }
    }

}