<?php

/**
 * This file is part of MetaModels/attribute_color.
 *
 * (c) 2012-2017 The MetaModels team.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    MetaModels
 * @subpackage AttributeColor
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     Andreas Isaak <info@andreas-isaak.de>
 * @author     Stefan Heimes <stefan_heimes@hotmail.com>
 * @author     Cliff Parnitzky <github@cliff-parnitzky.de>
 * @author     Ingolf Steinhardt <info@e-spin.de>
 * @author     David Molineus <david.molineus@netzmacht.de>
 * @copyright  2012-2017 The MetaModels team.
 * @license    https://github.com/MetaModels/attribute_color/blob/master/LICENSE LGPL-3.0
 * @filesource
 */

namespace MetaModels\AttributeColorBundle\Attribute;

use MetaModels\Attribute\BaseSimple;

/**
 * This is the MetaModelAttribute class for handling color fields.
 */
class Color extends BaseSimple
{
    /**
     * {@inheritDoc}
     */
    public function getSQLDataType()
    {
        return 'TINYBLOB NULL';
    }

    /**
     * {@inheritDoc}
     */
    public function getAttributeSettingNames()
    {
        return array_merge(
            parent::getAttributeSettingNames(),
            array(
                'flag',
                'searchable',
                'filterable',
                'sortable',
                'mandatory'
            )
        );
    }

    /**
     * {@inheritDoc}
     */
    public function getFieldDefinition($arrOverrides = array())
    {
        $arrFieldDef = parent::getFieldDefinition($arrOverrides);

        $arrFieldDef['inputType']              = 'text';
        $arrFieldDef['eval']['maxlength']      = 6;
        $arrFieldDef['eval']['size']           = 2;
        $arrFieldDef['eval']['multiple']       = true;
        $arrFieldDef['eval']['colorpicker']    = empty($arrFieldDef['eval']['readonly']);
        $arrFieldDef['eval']['isHexColor']     = true;
        $arrFieldDef['eval']['decodeEntities'] = true;
        $arrFieldDef['eval']['tl_class']      .= ' wizard inline';

        return $arrFieldDef;
    }

    /**
     * {@inheritdoc}
     *
     * This base implementation does a plain SQL sort by native value as defined by MySQL.
     */
    public function sortIds($idList, $strDirection)
    {
        $column    = $this->getColName();
        $statement = $this->connection->createQueryBuilder()
            ->select('id')
            ->addSelect($column)
            ->from($this->getMetaModel()->getTableName())
            ->where('id IN (:ids)')
            ->setParameter('ids', $idList)
            ->execute();

        $idList = array();
        while ($values = $statement->fetch(\PDO::FETCH_OBJ)) {
            $idList[$values->id] = $this->unserializeData($values->$column);
        }

        $sorted = $this->colorSort($idList, ('DESC' === $strDirection));

        return array_values($sorted);
    }

    /**
     * Sort a list of values by color value.
     *
     * @param array $colors     The colors to sort, indexed by item id.
     *
     * @param bool  $descending The sort direction, true if descending, false if ascending.
     *
     * @return array
     */
    private function colorSort($colors, $descending)
    {
        $counter = 0;
        $sorted  = array();
        foreach ($colors as $itemId => $colorValue) {
            $colorVal = $this->convertColorToSortValue($colorValue[0]);
            $colorSat = str_pad($colorValue[1], 3, '0', STR_PAD_LEFT);

            $sorted['_' . $colorVal . $colorSat . $counter] = $itemId;
            $counter++;
        }

        if ($descending) {
            krsort($sorted);
        } else {
            ksort($sorted);
        }

        return $sorted;
    }

    /**
     * Convert a color code to a sort value.
     *
     * @param string $colorValue The color value to convert.
     *
     * @return string
     */
    private function convertColorToSortValue($colorValue)
    {
        // Space is ASC 0x20 and is before '0' (which has ASC 0x30)
        if (strlen($colorValue) == 0) {
            return '     ';
        }

        if (strlen($colorValue) == 6) {
            $colorValue = $colorValue[0] . $colorValue[2] . $colorValue[4];
        }

        return str_pad(hexdec($colorValue), 5, '0', STR_PAD_LEFT);
    }

    /**
     * Take the raw data from the DB column and unserialize it.
     *
     * @param string|null $value The input value.
     *
     * @return array
     */
    public function unserializeData($value)
    {
        if (null === $value) {
            return array('', '');
        }

        return unserialize($value);
    }

    /**
     * Take the unserialized data and serialize it for the native DB column.
     *
     * @param array $value The input value.
     *
     * @return string|null
     */
    public function serializeData($value)
    {
        if (!($value[0] || $value[1])) {
            return null;
        }

        return serialize($value);
    }
}
