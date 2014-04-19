<?php

/**
 * The MetaModels extension allows the creation of multiple collections of custom items,
 * each with its own unique set of selectable attributes, with attribute extendability.
 * The Front-End modules allow you to build powerful listing and filtering of the
 * data in each collection.
 *
 * PHP version 5
 * @package     MetaModels
 * @subpackage  AttributeColor
 * @author      Stefan Heimes <cms@men-at-work.de>
 * @copyright   The MetaModels team.
 * @license     LGPL.
 * @filesource
 */

namespace MetaModels\Attribute\Color;

use MetaModels\Attribute\BaseSimple;

/**
 * This is the MetaModelAttribute class for handling color fields.
 *
 * @package	   MetaModels
 * @subpackage AttributeColor
 * @author     Stefan Heimes <cms@men-at-work.de>
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
		return array_merge(parent::getAttributeSettingNames(), array(
			'flag',
			'searchable',
			'filterable',
			'sortable',
			'mandatory'
		));
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
		$arrFieldDef['eval']['isHexColor']     = true;
		$arrFieldDef['eval']['decodeEntities'] = true;
		$arrFieldDef['eval']['tl_class']      .= ' wizard inline';

		// Version compare for color picker - from Contao 3 on this is handled as event.
		if (version_compare(3, VERSION, '<'))
		{
			$arrFieldDef['eval']['colorpicker'] = true;
		}

		return $arrFieldDef;
	}
}