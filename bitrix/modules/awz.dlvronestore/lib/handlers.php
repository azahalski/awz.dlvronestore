<?php

namespace Awz\Dlvronestore;

use Bitrix\Main\Application;
use Bitrix\Main\Config\Option;

class Handlers {

    public static function OnSaleComponentOrderJsData(&$arResult){
        if(
            class_exists('\\SaleOrderAjax') && class_exists('\\Awz\\Dlvronestore\\AwzCheckOneStoreDeliveryRestrictions')
            && AwzCheckOneStoreDeliveryRestrictions::$msg
        ){
            if(!is_array($arResult['JS_DATA']['WARNING'][\SaleOrderAjax::DELIVERY_BLOCK]))
                $arResult['JS_DATA']['WARNING'][\SaleOrderAjax::DELIVERY_BLOCK] = [];
            $arResult['JS_DATA']['WARNING'][\SaleOrderAjax::DELIVERY_BLOCK][] = AwzCheckOneStoreDeliveryRestrictions::$msg;
            $arResult['DOST_TEXT'] = AwzCheckOneStoreDeliveryRestrictions::$msg;
        }elseif(
            class_exists('\\SaleOrderAjax') && class_exists('\\Awz\\Dlvronestore\\AwzCheckOneStoreDeliveryRestrictions')
            && AwzCheckOneStoreDeliveryRestrictions::$msg2
        ){
            $allowedStoreIds = AwzCheckOneStoreDeliveryRestrictions::getLastStoreCheck();
            $newStores = [];
            $checkOutStock = false;
            if (!empty($arResult['JS_DATA']['STORE_LIST']) && !empty($allowedStoreIds)) {
                foreach ($arResult['JS_DATA']['STORE_LIST'] as $key => $store) {
                    if (in_array($store['ID'], $allowedStoreIds)) {
                        $newStores[$key] = $store;
                    }else{
                        $checkOutStock = true;
                    }
                }
                $arResult['JS_DATA']['STORE_LIST'] = $newStores;
            }
            if($checkOutStock){
                if(!is_array($arResult['JS_DATA']['WARNING'][\SaleOrderAjax::DELIVERY_BLOCK]))
                    $arResult['JS_DATA']['WARNING'][\SaleOrderAjax::DELIVERY_BLOCK] = [];
                $arResult['JS_DATA']['WARNING'][\SaleOrderAjax::DELIVERY_BLOCK][] = AwzCheckOneStoreDeliveryRestrictions::$msg2;
                $arResult['DOST_TEXT'] = AwzCheckOneStoreDeliveryRestrictions::$msg2;
            }
        }
    }

    public static function OnSaleComponentOrderDeliveriesCalculated($order, $arUserResult, $request, $arParams, &$arResult){
        if(
            class_exists('\\SaleOrderAjax') && class_exists('\\Awz\\Dlvronestore\\AwzCheckOneStoreDeliveryRestrictions')
            && AwzCheckOneStoreDeliveryRestrictions::$msg
        ){
            if(!is_array($arResult['JS_DATA']['WARNING'][\SaleOrderAjax::DELIVERY_BLOCK]))
                $arResult['JS_DATA']['WARNING'][\SaleOrderAjax::DELIVERY_BLOCK] = [];
            $arResult['JS_DATA']['WARNING'][\SaleOrderAjax::DELIVERY_BLOCK][] = AwzCheckOneStoreDeliveryRestrictions::$msg;
            $arResult['DOST_TEXT'] = AwzCheckOneStoreDeliveryRestrictions::$msg;
        }elseif(
            class_exists('\\SaleOrderAjax') && class_exists('\\Awz\\Dlvronestore\\AwzCheckOneStoreDeliveryRestrictions')
            && AwzCheckOneStoreDeliveryRestrictions::$msg2
        ){
            $allowedStoreIds = AwzCheckOneStoreDeliveryRestrictions::getLastStoreCheck();
            $newStores = [];
            $checkOutStock = false;
            if (!empty($arResult['JS_DATA']['STORE_LIST']) && !empty($allowedStoreIds)) {
                foreach ($arResult['JS_DATA']['STORE_LIST'] as $key => $store) {
                    if (in_array($store['ID'], $allowedStoreIds)) {
                        $newStores[$key] = $store;
                    }else{
                        $checkOutStock = true;
                    }
                }
                $arResult['JS_DATA']['STORE_LIST'] = $newStores;
            }
            if($checkOutStock){
                if(!is_array($arResult['JS_DATA']['WARNING'][\SaleOrderAjax::DELIVERY_BLOCK]))
                    $arResult['JS_DATA']['WARNING'][\SaleOrderAjax::DELIVERY_BLOCK] = [];
                $arResult['JS_DATA']['WARNING'][\SaleOrderAjax::DELIVERY_BLOCK][] = AwzCheckOneStoreDeliveryRestrictions::$msg2;
                $arResult['DOST_TEXT'] = AwzCheckOneStoreDeliveryRestrictions::$msg2;
            }
        }
    }


}