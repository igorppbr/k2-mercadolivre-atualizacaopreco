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
            $preco = $product->getFinalPrice()+28;
            $frete = 1;
            $anuncioML = "171";
        }
        else if($type==2){
            $preco = $product->getFinalPrice() * 0.9333;
            $frete = 0;
            $anuncioML = "159";
        }
        else if($type==3){
            $preco = $product->getFinalPrice() * 0.9333 + 28;
            $frete = 1;
            $anuncioML = "159";
        }

        $productBundle->setPrice($preco);
        $productBundle->setPriceMl($preco);
        $productBundle->setTipoAnuncioml($anuncioML);
        $productBundle->setFreteMl($frete);

        return $productBundle->save();

    }

}