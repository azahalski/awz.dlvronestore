<?php
namespace Awz\Dlvronestore;

use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;

Loader::includeModule('sale');

Loc::loadMessages(__FILE__);

class AwzCheckOneStoreDeliveryRestrictions extends \Bitrix\Sale\Delivery\Restrictions\Base {

    public static $msg = "";
    public static $msg2 = "";
    public static $storeIds = [];

    public static function getLastStoreCheck():array
    {
        return self::$storeIds;
    }

    public static function AwzCheckOneStoreDeliveryRestrictions()
    {
        return new \Bitrix\Main\EventResult(
            \Bitrix\Main\EventResult::SUCCESS,
            array(
                '\\Awz\\Dlvronestore\\AwzCheckOneStoreDeliveryRestrictions' => __FILE__,
            )
        );
    }

    /**
     * @return string
     */
    protected static function getJsHandler(): string
    {
        return 'BX.Sale.Delivery';
    }

    public static function getClassTitle(): string
    {
        return Loc::getMessage('AWZ_DLVRONESTORE_HANDLER_NAME');
    }

    public static function getClassDescription(): string
    {
        return Loc::getMessage('AWZ_DLVRONESTORE_HANDLER_DESC');
    }

    public static function getStoreList(): array
    {
        if(!\Bitrix\Main\Loader::includeModule('catalog')) return [];
        static $storeList = null;
        if($storeList === null){
            $storeList = [];
            $arStoreInfoRes = \CCatalogStore::GetList(
                ['SORT' => 'ASC'],
                ['ACTIVE' => 'Y'],
                false,
                false,
                ['ID', 'TITLE', 'ADDRESS', 'GPS_N', 'GPS_S', 'PHONE']
            );
            while($data = $arStoreInfoRes->fetch()){
                $storeList[$data['ID']] = '['.$data['ID'].'] - '.$data['TITLE'];
            }
        }
        return $storeList;
    }

    /**
     * Returns restriction params
     * @param int $entityId
     * @return array
     */
    public static function getParamsStructure($entityId = 0) : array
    {

        if(!\Bitrix\Main\Loader::includeModule('sale')) return [];
        if(!\Bitrix\Main\Loader::includeModule('catalog')) return [];

        $prmAr = [];
        $prmAr['MSG'] = [
            "TYPE" => "STRING",
            'MULTIPLE' => 'N',
            "LABEL" => Loc::getMessage('AWZ_DLVRONESTORE_HANDLER_MSG1'),
        ];
        $prmAr['MSG2'] = [
            "TYPE" => "STRING",
            'MULTIPLE' => 'N',
            "LABEL" => Loc::getMessage('AWZ_DLVRONESTORE_HANDLER_MSG2'),
        ];
        $prmAr['STORES'] = [
            "TYPE" => "ENUM",
            'MULTIPLE' => 'Y',
            "OPTIONS" => static::getStoreList(),
            "LABEL" => Loc::getMessage('AWZ_DLVRONESTORE_HANDLER_STORE_LABEL'),
        ];

        return $prmAr;
    }

    /**
     * Retrieves from the $entity an array
     * @param \Bitrix\Sale\Internals\Entity $entity
     * @return array
     */
    public static function extractParams(\Bitrix\Sale\Internals\Entity $entity) : array
    {
        if (!\Bitrix\Main\Loader::includeModule('catalog'))
            return [];

        $basketItems = static::getBasketItems($entity);

        $productIds = [];

        /** @var \Bitrix\Sale\BasketItem $basketItem */
        foreach ($basketItems as $basketItem)
        {
            if ($basketItem->getField('MODULE') != 'catalog')
            {
                continue;
            }

            $productId = (int)$basketItem->getField('PRODUCT_ID');
            //$productInfo = \CCatalogSKU::getProductInfo($productId);
            //$candidate = $productInfo['ID'] ?? $productId;
            $candidate = $productId;

            if (!isset($productIds[$candidate]))
            {
                $productIds[$candidate] = 0;
            }

            $productIds[$candidate] += $basketItem->getQuantity();
        }

        /* @var $entity \Bitrix\Sale\Shipment */

        return [$productIds,$entity->getOrder()];
    }

    /**
     * Compares the list of categories of items in basket with the list of categories
     * that restrict entity and returns true if all basket categories exist in restriction list
     * @param array $entityData array of product Ids that are in the basket
     * @param array $restrictionParams
     * @param int $deliveryId
     * @return bool
     */
    public static function check($entityData, array $restrictionParams, $deliveryId = 0) : bool
    {
        self::$msg2 = '';
        self::$msg = '';
        if (
            empty($entityData) || !is_array($entityData) || count($entityData)!=2
            || !is_array($entityData[0])
            || !($entityData[1] instanceof \Bitrix\Sale\Order)
        )
        {
            return true;
        }

        if(empty($restrictionParams['STORES']))
            return true;

        /* @var $order \Bitrix\Sale\Order */
        list($products, $order) = $entityData;

        $activeStores = static::getActiveStores($products, $restrictionParams['STORES']);

        if(empty($activeStores)){
            self::$msg = $restrictionParams['MSG'];
            return false;
        }

        self::$storeIds = $activeStores;
        self::$msg2 = $restrictionParams['MSG2'];

        return true;
    }

    protected static function getActiveStores(array $products = [], array $activeStores = []): array
    {
        $prodCount = count($products);
        $activeStores = [];
        $storeProductCnt = [];
        foreach($products as $prodId=>$prodCnt){
            // Получаем остатки по складам
            $storeRes = \CCatalogStoreProduct::GetList(
                [],
                ['PRODUCT_ID' => $prodId, '>=AMOUNT' => $prodCnt, 'STORE_ID'=>$activeStores],
                false,
                false,
                ['ID', 'PRODUCT_ID', 'STORE_ID', 'AMOUNT']
            );

            while ($store = $storeRes->Fetch()) {
                $storeProductCnt[$store['STORE_ID']] = $storeProductCnt[$store['STORE_ID']] ?? 0;
                $storeProductCnt[$store['STORE_ID']] += 1;
                if($prodCount == $storeProductCnt[$store['STORE_ID']])
                    $activeStores[] = $store['STORE_ID'];
            }
        }

        return $activeStores;
    }

    /**
     * @param Shipment $entity
     * @return array
     */
    protected static function getBasketItems(\Bitrix\Sale\Internals\Entity $entity): array
    {
        if (!$entity instanceof \Bitrix\Sale\Shipment)
            return [];

        $basketItems = [];

        /** @var \Bitrix\Sale\ShipmentItem $shipmentItem */
        foreach ($entity->getShipmentItemCollection()->getSellableItems() as $shipmentItem)
        {
            $basketItems[] = $shipmentItem->getBasketItem();
        }

        return $basketItems;
    }

}