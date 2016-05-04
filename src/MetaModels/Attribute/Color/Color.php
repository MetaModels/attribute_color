<?php

/**
 * This file is part of MetaModels/attribute_color.
 *
 * (c) 2012-2016 The MetaModels team.
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
 * @copyright  2012-2016 The MetaModels team.
 * @license    https://github.com/MetaModels/attribute_color/blob/master/LICENSE LGPL-3.0
 * @filesource
 */

namespace MetaModels\Attribute\Color;

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
        $arrFieldDef['eval']['colorpicker']    = true;
        $arrFieldDef['eval']['isHexColor']     = true;
        $arrFieldDef['eval']['decodeEntities'] = true;
        $arrFieldDef['eval']['tl_class']      .= ' wizard inline';

        return $arrFieldDef;
    }
}
