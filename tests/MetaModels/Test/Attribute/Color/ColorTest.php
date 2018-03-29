<?php

/**
 * This file is part of MetaModels/attribute_color.
 *
 * (c) 2012-2018 The MetaModels team.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    MetaModels
 * @subpackage AttributeColor
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @copyright  2012-2018 The MetaModels team.
 * @license    https://github.com/MetaModels/attribute_color/blob/master/LICENSE LGPL-3.0
 * @filesource
 */

namespace MetaModels\Test\Attribute\Color;

use Contao\Database;
use MetaModels\Attribute\Color\Color;
use MetaModels\MetaModelsServiceContainer;
use PHPUnit\Framework\TestCase;
use Contao\Database\Statement;
use Contao\Database\Result;
use MetaModels\IMetaModel;

/**
 * Unit tests to test class Color.
 */
class ColorTest extends TestCase
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
            ->getMockBuilder(Database::class)
            ->disableOriginalConstructor()
            ->setMethods(['__destruct'])
            ->getMockForAbstractClass();

        $mockDb->method('createStatement')->willReturn(
            $statement = $this
                ->getMockBuilder(Statement::class)
                ->disableOriginalConstructor()
                ->setMethods(['debugQuery', 'createResult'])
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
            $result = ['ignored'];
        } else {
            $result = (object) $result;
        }

        $statement->method('execute_query')->willReturn($result);
        $statement->method('createResult')->willReturnCallback(
            function ($resultData) {
                $index = 0;

                $resultData = (array) $resultData;

                $resultSet = $this
                    ->getMockBuilder(Result::class)
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
        $metaModel = $this->getMockBuilder(IMetaModel::class)->getMockForAbstractClass();

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
        $this->assertInstanceOf(Color::class, $text);
    }

    /**
     * Provide array data for sorting.
     *
     * @return array
     */
    public function provideSortArray()
    {
        return [
            [
                'expected' => [6, 12, 11, 10, 9, 7, 8, 5, 4, 3, 2, 1],
                'data'     => [
                    ['id' => 1, 'color' => serialize(['fafa05', ''])],
                    ['id' => 2, 'color' => serialize(['fa8405', ''])],
                    ['id' => 3, 'color' => serialize(['f605fa', ''])],
                    ['id' => 4, 'color' => serialize(['fa053a', '80'])],
                    ['id' => 5, 'color' => serialize(['fa053a', '20'])],
                    ['id' => 6, 'color' => serialize(['', ''])],
                    ['id' => 7, 'color' => serialize(['333', ''])],
                    ['id' => 8, 'color' => serialize(['333333', ''])],
                    ['id' => 9, 'color' => serialize(['05fafa', ''])],
                    ['id' => 10, 'color' => serialize(['05fa63', ''])],
                    ['id' => 11, 'color' => serialize(['0511fa', ''])],
                    ['id' => 12, 'color' => serialize(['000000', ''])],
                ]
            ]
        ];
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
        $ids = [];
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

        $color = new Color($metaModel, ['colname' => 'color']);

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
        $ids = [];
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

        $color = new Color($metaModel, ['colname' => 'color']);

        $this->assertEquals(array_reverse($expected), $color->sortIds($ids, 'DESC'));
    }
}
