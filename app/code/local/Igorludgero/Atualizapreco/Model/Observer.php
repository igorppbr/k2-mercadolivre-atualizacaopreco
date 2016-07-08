<?php
/**
 * Created by PhpStorm.
 * User: igorludgeromiura
 * Date: 04/07/16
 * Time: 16:55
 */

class Igorludgero_Atualizapreco_Model_Observer {

    public function checkPrices($observer){
        $productId = $observer->getProduct()->getId();
        $model = Mage::getModel('catalog/product');
        $_product = $model->load($productId);
        if($_product->getTypeId() == 'simple'){
            $skuA = $_product->getSku()."-bundleA";
            $skuB = $_product->getSku()."-bundleB";
            $skuC = $_product->getSku()."-bundleC";
            $bundleA = Mage::getModel('catalog/product')->loadByAttribute('sku',$skuA);
            $bundleB = Mage::getModel('catalog/product')->loadByAttribute('sku',$skuB);
            $bundleC = Mage::getModel('catalog/product')->loadByAttribute('sku',$skuC);
            $helper = Mage::helper('atualizapreco');

            $contador = 0;

            if($bundleA!=null) {

                if ($bundleA->getData() != null) {
                    //Mage::log("abriu o bundleA: ".$bundleA->getSku(),null,"il_atualizacaopreco.log");
                    if ($helper->updatePrice($_product, $bundleA, 1))
                        $contador++;
                }
            }

            if($bundleB!=null) {

                if ($bundleB->getData() != null) {
                    //Mage::log("abriu o bundleB",null,"il_atualizacaopreco.log");
                    if ($helper->updatePrice($_product, $bundleB, 2))
                        $contador++;
                }
            }

            if($bundleC) {
                
                if ($bundleC->getData() != null) {
                    //Mage::log("abriu o bundleC",null,"il_atualizacaopreco.log");
                    if ($helper->updatePrice($_product, $bundleC, 3))
                        $contador++;
                }
            }

            $idsOtherBundles = $helper->findBundlesOfSimple($_product->getId());
            if(count($idsOtherBundles)>0){
                foreach($idsOtherBundles as $idBundle){
                    $contador = $contador + $helper->updateBundlePriceMl($idBundle);
                }
            }

        }
        if($contador)
            Mage::getSingleton('core/session')->addSuccess($contador." bundles relacionados a este produto foram atualizados.");

    }

    public function checkPricesCsv($observer){
        Mage::log("atualizou",null,"iludgero_atualizapreco.log");
        $adapter = $observer->getEvent()->getAdapter();
        $affectedEntityIds = $adapter->getAffectedEntityIds();

        $contador = 0;
        $model = Mage::getModel('catalog/product');

        foreach($affectedEntityIds as $id){
            $_product = $model->load($id);
            if($_product->getTypeId() == 'simple'){
                $skuA = $_product->getSku()."-bundleA";
                $skuB = $_product->getSku()."-bundleB";
                $skuC = $_product->getSku()."-bundleC";
                $bundleA = Mage::getModel('catalog/product')->loadByAttribute('sku',$skuA);
                $bundleB = Mage::getModel('catalog/product')->loadByAttribute('sku',$skuB);
                $bundleC = Mage::getModel('catalog/product')->loadByAttribute('sku',$skuC);
                $helper = Mage::helper('atualizapreco');

                if($bundleA!=null) {

                    if ($bundleA->getData() != null) {
                        //Mage::log("abriu o bundleA: ".$bundleA->getSku(),null,"il_atualizacaopreco.log");
                        if ($helper->updatePrice($_product, $bundleA, 1))
                            $contador++;
                    }
                }

                if($bundleB!=null) {

                    if ($bundleB->getData() != null) {
                        //Mage::log("abriu o bundleB",null,"il_atualizacaopreco.log");
                        if ($helper->updatePrice($_product, $bundleB, 2))
                            $contador++;
                    }
                }

                if($bundleC) {

                    if ($bundleC->getData() != null) {
                        //Mage::log("abriu o bundleC",null,"il_atualizacaopreco.log");
                        if ($helper->updatePrice($_product, $bundleC, 3))
                            $contador++;
                    }
                }

                $idsOtherBundles = $helper->findBundlesOfSimple($_product->getId());
                if(count($idsOtherBundles)>0){
                    foreach($idsOtherBundles as $idBundle){
                        $contador = $contador + $helper->updateBundlePriceMl($idBundle);
                    }
                }

            }

        }
        if($contador)
            Mage::getSingleton('core/session')->addSuccess($contador." bundles relacionados a este produto foram atualizados.");

    }

}