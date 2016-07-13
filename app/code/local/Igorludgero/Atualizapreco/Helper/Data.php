<?php

class Igorludgero_Atualizapreco_Helper_Data extends Mage_Core_Helper_Abstract
{

    public function updatePrice($product,$productBundle,$type){

        //Mage::log($product->getSku(),null,"il_atualizacaopreco.log");
        //Mage::log($productBundle->getSku(),null,"il_atualizacaopreco.log");
        //Mage::log($type,null,"il_atualizacaopreco.log");

        $anuncioML = "";
        $frete = "";
        $preco = "";

        if($type==1){
            $preco = ($product->getFinalPrice() * 0.9 / 0.84) + 28;
            $frete = 1;
            $anuncioML = "171";
        }
        else if($type==2){
            $preco = $product->getFinalPrice() * 0.9 / 0.84;
            $frete = 0;
            $anuncioML = "171";
        }
        else if($type==3){
            $preco = $product->getFinalPrice() + 28;
            $frete = 1;
            $anuncioML = "159";
        }

        $productBundle->setPrice($preco);
        $productBundle->setPriceMl($preco);
        $productBundle->setTipoAnuncioml($anuncioML);
        $productBundle->setFreteMl($frete);

        return $productBundle->save();

    }

    function findBundlesOfSimple($id_to_find)
    {
        //grab all bundled products
        $bundles = array();
        $products = Mage::getModel('catalog/product')
            ->getCollection()
            ->addFieldToFilter('type_id','bundle');

        //loop over bundled products
        foreach($products as $product)
        {


            //get child product IDs
            $children_ids_by_option = $product
                ->getTypeInstance($product)
                ->getChildrenIds($product->getId(),false); //second boolean "true" will return only
            //required child products instead of all

            //flatten arrays (which are grouped by option)
            $ids = array();
            foreach($children_ids_by_option as $array)
            {
                $ids = array_merge($ids, $array);
            }

            //perform test and add to return value
            if(in_array($id_to_find, $ids))
            {
                if(strpos($product->getSku(), 'bundle') == false)
                    $bundles[] = $product->getId();
            }


        }

        return $bundles;
    }

    function updateBundlePriceMl($bundleId,$special_price){
        $_product = Mage::getModel('catalog/product')->load($bundleId);
        $selectionCollection = $_product->getTypeInstance(true)->getSelectionsCollection(
            $_product->getTypeInstance(true)->getOptionsIds($_product), $_product
        );

        $bundled_items = array();
        foreach ($selectionCollection as $option) {
            $bundled_items[] = $option->product_id;
        }

        $totalPrice = 0;

        foreach ($bundled_items as $productId) {
            $currentSimple = Mage::getModel('catalog/product')->load($productId);
            $totalPrice = $totalPrice + $currentSimple->getFinalPrice();
        }

        $priceMl = 0;

        Mage::log("totalPrice: ".$totalPrice,null,"il_atualizapreco.log");
        Mage::log("specialPrice: ".$special_price,null,"il_atualizapreco.log");

        if(floatval($special_price)>0) {
            Mage::log("preco special eh maior que zero",null,"il_atualizapreco.log");
            $priceMl = $totalPrice * $special_price / 100;
        }
        else {
            Mage::log("preco special eh menor ou igual a zero",null,"il_atualizapreco.log");
            $priceMl = $totalPrice;
        }

        Mage::log("priceMl: ".$priceMl,null,"il_atualizapreco.log");

        return $priceMl;

    }

    public function updatebundles($productId)
    {
        try {
            $model = Mage::getModel('catalog/product');
                    $contProd = 0;
                    $idsOtherBundles = $this->findBundlesOfSimple($productId);
                    Mage::log("ids dos bundles que pertence", null, "il_atualizapreco_cron.log");
                    Mage::log($idsOtherBundles, null, "il_atualizapreco_cron.log");
                    if (count($idsOtherBundles) > 0) {
                        foreach ($idsOtherBundles as $idBundle) {
                            $priceMl = $this->updateBundlePriceMl($idBundle);
                            $_productBundle = $model->load($idBundle);
                            if (strpos($_productBundle->getSku(), 'bundle') == false) {
                                //Mage::log("vai salvar bundle atualizacao, id: " . $_productBundle->getName(), null, "il_atualizapreco_cron.log");
                                $_productBundle->setData('price_ml', $priceMl);
                                $_productBundle->getResource()->saveAttribute($_productBundle, 'price_ml');
                                Mage::log("priceMl " . $priceMl . " salvo no produto de sku " . $_productBundle->getSku(), null, "il_atualizapreco_cron.log");
                            }
                        }
                        return true;
                    }
            else{
                return true;
            }
        }
        catch(Exception $ex){
            Mage::log("erro ao dar update nos bundles do simples: ".$ex->getMessage(),null,"il_atualizapreco_cron.log");
        }
        return false;
    }

}