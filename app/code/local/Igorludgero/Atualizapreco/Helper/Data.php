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

    function updateBundlePriceMl($bundleId){
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

        if($_product->getSpecialPrice())
            $priceMl = $totalPrice * $_product->getSpecialPrice() / 100;
        else
            $priceMl = $totalPrice;

        $_product->setPriceMl($priceMl);

        if($_product->save())
            return 1;
        return 0;

    }

}