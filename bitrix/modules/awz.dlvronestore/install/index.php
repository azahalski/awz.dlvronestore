<?php
use Bitrix\Main\Localization\Loc,
    Bitrix\Main\EventManager,
    Bitrix\Main\ModuleManager,
	Bitrix\Main\Config\Option,
    Bitrix\Main\Application;

Loc::loadMessages(__FILE__);

class awz_dlvronestore extends CModule
{
	var $MODULE_ID = "awz.dlvronestore";
	var $MODULE_VERSION;
	var $MODULE_VERSION_DATE;
	var $MODULE_NAME;
	var $MODULE_DESCRIPTION;

    public function __construct()
	{
        $arModuleVersion = array();
        include(__DIR__.'/version.php');

        $this->MODULE_VERSION = $arModuleVersion["VERSION"];
        $this->MODULE_VERSION_DATE = $arModuleVersion["VERSION_DATE"];

        $this->MODULE_NAME = Loc::getMessage("AWZ_DLVRONESTORE_MODULE_NAME");
        $this->MODULE_DESCRIPTION = Loc::getMessage("AWZ_DLVRONESTORE_MODULE_DESCRIPTION");
		$this->PARTNER_NAME = Loc::getMessage("AWZ_PARTNER_NAME");
		$this->PARTNER_URI = "https://zahalski.dev/";

		return true;
	}

    function DoInstall()
    {
        global $APPLICATION, $step;

        $this->InstallFiles();
        $this->InstallDB();
        $this->checkOldInstallTables();
        $this->InstallEvents();
        $this->createAgents();

        ModuleManager::RegisterModule($this->MODULE_ID);

        $APPLICATION->IncludeAdminFile(
            Loc::getMessage("AWZ_DLVRONESTORE_MODULE_NAME"),
            $_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/'. $this->MODULE_ID .'/install/solution.php'
        );

        return true;
    }

    function DoUninstall()
    {
        global $APPLICATION, $step;

        $step = intval($step);
        if($step < 2) {
            $APPLICATION->IncludeAdminFile(
                Loc::getMessage('AWZ_DLVRONESTORE_INSTALL_TITLE'),
                $_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/'. $this->MODULE_ID .'/install/unstep.php'
            );
        }
        elseif($step == 2) {
            if($_REQUEST['save'] != 'Y' && !isset($_REQUEST['save'])) {
                $this->UnInstallDB();
            }
            $this->UnInstallFiles();
            $this->UnInstallEvents();
            $this->deleteAgents();

            if($_REQUEST['saveopts'] != 'Y' && !isset($_REQUEST['saveopts'])) {
                \Bitrix\Main\Config\Option::delete($this->MODULE_ID);
            }

            ModuleManager::UnRegisterModule($this->MODULE_ID);
            return true;
        }
		
    }

    function InstallDB()
    {
        return true;
    }

    function UnInstallDB()
    {
        return true;
    }

    function InstallEvents()
    {
        $eventManager = EventManager::getInstance();
        $eventManager->registerEventHandlerCompatible("sale", "OnSaleComponentOrderJsData",
            $this->MODULE_ID, '\\Awz\\Dlvronestore\\Handlers', 'OnSaleComponentOrderJsData'
        );
        $eventManager->registerEventHandlerCompatible("sale", "OnSaleComponentOrderDeliveriesCalculated",
            $this->MODULE_ID, '\\Awz\\Dlvronestore\\Handlers', 'OnSaleComponentOrderDeliveriesCalculated'
        );
        $eventManager->registerEventHandler("sale", "onSaleDeliveryRestrictionsClassNamesBuildList",
            $this->MODULE_ID, '\\Awz\\Dlvronestore\\AwzCheckOneStoreDeliveryRestrictions', 'AwzCheckOneStoreDeliveryRestrictions'
        );
        return true;
    }

    function UnInstallEvents()
    {
        $eventManager = EventManager::getInstance();
        $eventManager->unRegisterEventHandler(
            'sale', 'OnSaleComponentOrderJsData',
            $this->MODULE_ID, '\\Awz\\Dlvronestore\\AwzCheckOneStoreDeliveryRestrictions', 'OnSaleComponentOrderJsData'
        );
        $eventManager->unRegisterEventHandler(
            'sale', 'OnSaleComponentOrderDeliveriesCalculated',
            $this->MODULE_ID, '\\Awz\\Dlvronestore\\Handlers', 'OnSaleComponentOrderDeliveriesCalculated'
        );
        $eventManager->unRegisterEventHandler(
            'sale', 'OnSaleComponentOrderDeliveriesCalculated',
            $this->MODULE_ID, '\\Awz\\Dlvronestore\\AwzCheckOneStoreDeliveryRestrictions', 'AwzCheckOneStoreDeliveryRestrictions'
        );
        return true;
    }

    function InstallFiles()
    {
        return true;
    }

    function UnInstallFiles()
    {
        return true;
    }

    function createAgents() {
        return true;
    }

    function deleteAgents() {
        CAgent::RemoveModuleAgents($this->MODULE_ID);
        return true;
    }

    function checkOldInstallTables()
    {
        return true;
    }

}