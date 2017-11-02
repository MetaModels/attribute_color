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
 * @author     David Molineus <david.molineus@netzmacht.de>
 * @copyright  2012-2017 The MetaModels team.
 * @license    https://github.com/MetaModels/attribute_color/blob/master/LICENSE LGPL-3.0
 * @filesource
 */

namespace MetaModels\Test\Attribute\Color;

use Contao\Database;
use MetaModels\Attribute\Color\Color;
use MetaModels\MetaModelsServiceContainer;

/**
 * Unit tests to test class Color.
 */
class ColorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Mock the Contao database.
     *
     * @param string     $expectedQuery The query to expect.
     *
     * @param null|array $result        The resulting datasets.
     *
     * @return Database|\PHPUnit_Framework_MockObject_MockObject
     */
    private function mockDatabase($expectedQuery = '', $result = null)
    {
        $mockDb = $this
            ->getMockBuilder('Contao\Database')
            ->disableOriginalConstructor()
            ->setMethods(array('__destruct'))
            ->getMockForAbstractClass();

        $mockDb->method('createStatement')->willReturn(
            $statement = $this
                ->getMockBuilder('Contao\Database\Statement')
                ->disableOriginalConstructor()
                ->setMethods(array('debugQuery', 'createResult'))
                ->getMockForAbstractClass()
        );

        if (!$expectedQuery) {
            $statement->expects($this->never())->method('prepare_query');

            return $mockDb;
        }

        $statement
            ->expects($this->once())
            ->method('prepare_query')
            ->with($expectedQuery)
            ->willReturnArgument(0);

        if ($result === null) {
            $result = array('ignored');
        } else {
            $result = (object) $result;
        }

        $statement->method('execute_query')->willReturn($result);
        $statement->method('createResult')->willReturnCallback(
            function ($resultData) {
                $index = 0;

                $resultData = (array) $resultData;

                $resultSet = $this
                    ->getMockBuilder('Contao\Database\Result')
                    ->disableOriginalConstructor()
                    ->getMockForAbstractClass();

                $resultSet->method('fetch_row')->willReturnCallback(function () use (&$index, $resultData) {
                    return array_values($resultData[$index++]);
                });
                $resultSet->method('fetch_assoc')->willReturnCallback(function () use (&$index, $resultData) {
                    if (!isset($resultData[$index])) {
                        return false;
                    }
                    return $resultData[$index++];
                });
                $resultSet->method('num_rows')->willReturnCallback(function () use ($index, $resultData) {
                    return count($resultData);
                });
                $resultSet->method('num_fields')->willReturnCallback(function () use ($index, $resultData) {
                    return count($resultData[$index]);
                });
                $resultSet->method('fetch_field')->willReturnCallback(function ($field) use ($index, $resultData) {
                    $data = array_values($resultData[$index]);
                    return $data[$field];
                });
                $resultSet->method('data_seek')->willReturnCallback(function ($newIndex) use (&$index, $resultData) {
                    $index = $newIndex;
                });

                return $resultSet;
            }
        );

        return $mockDb;
    }

    /**
     * Mock a MetaModel.
     *
     * @param string   $language         The language.
     *
     * @param string   $fallbackLanguage The fallback language.
     *
     * @param Database $database         The database to use.
     *
     * @return \MetaModels\IMetaModel
     */
    protected function mockMetaModel($language, $fallbackLanguage, $database)
    {
        $metaModel = $this->getMock(
            'MetaModels\MetaModel',
            array(),
            array(array())
        );

        $metaModel
            ->expects($this->any())
            ->method('getTableName')
            ->will($this->returnValue('mm_unittest'));

        $metaModel
            ->expects($this->any())
            ->method('getActiveLanguage')
            ->will($this->returnValue($language));

        $metaModel
            ->expects($this->any())
            ->method('getFallbackLanguage')
            ->will($this->returnValue($fallbackLanguage));

        $serviceContainer = new MetaModelsServiceContainer();
        $serviceContainer->setDatabase($database);

        $metaModel
            ->method('getServiceContainer')
            ->willReturn($serviceContainer);

        return $metaModel;
    }

    /**
     * Test that the attribute can be instantiated.
     *
     * @return void
     */
    public function testInstantiation()
    {
        $text = new Color($this->mockMetaModel('en', 'en', $this->mockDatabase()));
        $this->assertInstanceOf('MetaModels\Attribute\Color\Color', $text);
    }

    /**
     * Provide array data for sorting.
     *
     * @return array
     */
    public function provideSortArray()
    {
        return array(
            array(
                'expected' => array(6, 12, 11, 10, 9, 7, 8, 5, 4, 3, 2, 1),
                'data'     => array(
                    array('id' => 1, 'color' => serialize(array('fafa05', ''))),
                    array('id' => 2, 'color' => serialize(array('fa8405', ''))),
                    array('id' => 3, 'color' => serialize(array('f605fa', ''))),
                    array('id' => 4, 'color' => serialize(array('fa053a', '80'))),
                    array('id' => 5, 'color' => serialize(array('fa053a', '20'))),
                    array('id' => 6, 'color' => serialize(array('', ''))),
                    array('id' => 7, 'color' => serialize(array('333', ''))),
                    array('id' => 8, 'color' => serialize(array('333333', ''))),
                    array('id' => 9, 'color' => serialize(array('05fafa', ''))),
                    array('id' => 10, 'color' => serialize(array('05fa63', ''))),
                    array('id' => 11, 'color' => serialize(array('0511fa', ''))),
                    array('id' => 12, 'color' => serialize(array('000000', ''))),
                )
            )
        );
    }

    /**
     * Test the sorting function in ascending order.
     *
     * @param array $expected The expected result order.
     *
     * @param array $data     The array to sort.
     *
     * @return void
     *
     * @dataProvider provideSortArray
     */
    public function testSortAsc($expected, $data)
    {
        $ids = array();
        foreach ($data as $item) {
            $ids[] = $item['id'];
        }

        $metaModel = $this->mockMetaModel(
            'en',
            'en',
            $this->mockDatabase(
                sprintf(
                    'SELECT id, color FROM mm_unittest WHERE id IN (%s);',
                    implode(',', array_fill(0, count($ids), '?'))
                ),
                $data
            )
        );

        $color = new Color($metaModel, array('colname' => 'color'));

        $this->assertEquals($expected, $color->sortIds($ids, 'ASC'));
    }

    /**
     * Test the sorting function in decending order.
     *
     * @param array $expected The expected result order.
     *
     * @param array $data     The array to sort.
     *
     * @return void
     *
     * @dataProvider provideSortArray
     */
    public function testSortDesc($expected, $data)
    {
        $ids = array();
        foreach ($data as $item) {
            $ids[] = $item['id'];
        }

        $metaModel = $this->mockMetaModel(
            'en',
            'en',
            $this->mockDatabase(
                sprintf(
                    'SELECT id, color FROM mm_unittest WHERE id IN (%s);',
                    implode(',', array_fill(0, count($ids), '?'))
                ),
                $data
            )
        );

        $color = new Color($metaModel, array('colname' => 'color'));

        $this->assertEquals(array_reverse($expected), $color->sortIds($ids, 'DESC'));
    }
}
