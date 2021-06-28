<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidEsales\EshopCommunity\Application\Controller\Admin;

use OxidEsales\Eshop\Core\DatabaseProvider;
use OxidEsales\Eshop\Core\Registry;

/**
 * Class manages article select lists sorting
 */
class SelectListOrderAjax extends \OxidEsales\Eshop\Application\Controller\Admin\ListComponentAjax
{
    /**
     * Columns array
     *
     * @var array
     */
    protected $_aColumns = ['container1' => [
        ['oxtitle', 'oxselectlist', 1, 1, 0],
        ['oxsort', 'oxobject2selectlist', 1, 0, 0],
        ['oxident', 'oxselectlist', 0, 0, 0],
        ['oxvaldesc', 'oxselectlist', 0, 0, 0],
        ['oxid', 'oxobject2selectlist', 0, 0, 1]
    ]
    ];

    /**
     * Returns SQL query for data to fetc
     *
     * @return string
     */
    protected function getQuery() // phpcs:ignore PSR2.Methods.MethodDeclaration.Underscore
    {
        $sSelTable = $this->getViewName('oxselectlist');
        $sArtId = Registry::getRequest()->getRequestEscapedParameter('oxid');

        return " from $sSelTable left join oxobject2selectlist on oxobject2selectlist.oxselnid = $sSelTable.oxid " .
                 "where oxobjectid = " . DatabaseProvider::getDb()->quote($sArtId) . " ";
    }

    /**
     * Returns SQL query addon for sorting
     *
     * @return string
     */
    protected function getSorting() // phpcs:ignore PSR2.Methods.MethodDeclaration.Underscore
    {
        return 'order by oxobject2selectlist.oxsort ';
    }

    /**
     * Applies sorting for selection lists
     */
    public function setSorting()
    {
        $sSelId = Registry::getRequest()->getRequestEscapedParameter('oxid');
        $sSelect = "select * from oxobject2selectlist where oxobjectid = :oxobjectid order by oxsort";

        $oList = oxNew(\OxidEsales\Eshop\Core\Model\ListModel::class);
        $oList->init("oxbase", "oxobject2selectlist");
        $oList->selectString($sSelect, [
            ':oxobjectid' => $sSelId
        ]);

        // fixing indexes
        $iSelCnt = 0;
        $aIdx2Id = [];
        foreach ($oList as $sKey => $oSel) {
            if ($oSel->oxobject2selectlist__oxsort->value != $iSelCnt) {
                $oSel->oxobject2selectlist__oxsort->setValue($iSelCnt);

                // saving new index
                $oSel->save();
            }
            $aIdx2Id[$iSelCnt] = $sKey;
            $iSelCnt++;
        }

        //
        if (($iKey = array_search(Registry::getRequest()->getRequestEscapedParameter('sortoxid'), $aIdx2Id)) !== false) {
            $iDir = (Registry::getRequest()->getRequestEscapedParameter('direction') == 'up') ? ($iKey - 1) : ($iKey + 1);
            if (isset($aIdx2Id[$iDir])) {
                // exchanging indexes
                $oDir1 = $oList->offsetGet($aIdx2Id[$iDir]);
                $oDir2 = $oList->offsetGet($aIdx2Id[$iKey]);

                $iCopy = $oDir1->oxobject2selectlist__oxsort->value;
                $oDir1->oxobject2selectlist__oxsort->setValue($oDir2->oxobject2selectlist__oxsort->value);
                $oDir2->oxobject2selectlist__oxsort->setValue($iCopy);

                $oDir1->save();
                $oDir2->save();
            }
        }

        $sQAdd = $this->getQuery();

        $sQ = 'select ' . $this->getQueryCols() . $sQAdd;
        $sCountQ = 'select count( * ) ' . $sQAdd;

        $this->outputResponse($this->getData($sCountQ, $sQ));
    }
}
