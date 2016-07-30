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
        $contador = 0;
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

            if($helper->updatebundles($_product->getId())){
                $contador++;
            }

            if($contador) {
                if ($contador > 0) {
                    Mage::getSingleton('core/session')->addSuccess($contador . " bundles relacionados a este produto foram atualizados.");
                }
            }

        }

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
                $_product->setPriceMl($_product->getFinalPrice());
                $_product->save();
            }

        }
        if($contador) {
            if ($contador > 0) {
                Mage::getSingleton('core/session')->addSuccess($contador . " bundles relacionados a este produto foram atualizados.");
            }
        }

    }

    public function setPriceMl($observer){
        $_product = $observer->getProduct();
        $data = $_product->getData();
        //$origData = $_product->getOrigData();
        //Mage::log($data,null,"data.log");
        //Mage::log($data,null,"newdata.log");
        if($_product->getTypeId() == 'simple') {
            Mage::log("vai setar price ml no produto de sku " . $_product->getSku(), null, "il_atualizapreco.log");
            $_product->setPriceMl($_product->getFinalPrice());
        }
        else if($_product->getTypeId() == 'bundle'){
            if (strpos($_product->getSku(), 'bundle') == false) {
                Mage::log("entrou no setPriceML bundle: " . $_product->getId(), null, "il_atualizapreco.log");
                $helper = Mage::helper('atualizapreco');
                $priceMl = $helper->updateBundlePriceMl($_product->getId(), $data['special_price']);
                $_product->setPriceMl($priceMl);
            }
        }
    }

    public function addMassactionToProductGrid($observer)
    {
        $block = $observer->getBlock();
        if($block instanceof Mage_Adminhtml_Block_Catalog_Product_Grid){

            $sets = Mage::getResourceModel('eav/entity_attribute_set_collection')
                ->setEntityTypeFilter(Mage::getModel('catalog/product')->getResource()->getTypeId())
                ->load()
                ->toOptionHash();

            $block->getMassactionBlock()->addItem('igorludgero_atualizabundle', array(
                'label'=> Mage::helper('catalog')->__('Atualizar bundles do produto'),
                'url'  => $block->getUrl('*/*/updatebundles', array('_current'=>true))
            ));
        }
    }

    public function updateBundlesAfterSimple(){
        try {
            $helper = Mage::helper('atualizapreco');
            Mage::log("vai executar o cron no updateBundlesAfterSimple", null, "il_atualizapreco_cron.log");
            $simpleCollection = Mage::getModel('catalog/product')->getCollection()->addFieldToFilter('type_id', 'simple')->getAllIds();
            Mage::log("ids de todos os bundles",null,"il_atualizapreco_cron.log");
            foreach($simpleCollection as $id){
                $helper->updatebundles($id);
            }
        }
        catch(Exception $ex){
            Mage::log("erro no cron no updateBundlesAfterSimple: ".$ex->getMessage(), null, "il_atualizapreco_cron.log");
        }
    }

}