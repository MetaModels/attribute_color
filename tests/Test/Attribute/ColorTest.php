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

namespace MetaModels\AttributeColorBundle\Test\Attribute;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Driver\Statement;
use Doctrine\DBAL\Query\QueryBuilder;
use MetaModels\AttributeColorBundle\Attribute\Color;
use MetaModels\Helper\TableManipulator;
use PHPUnit\Framework\TestCase;

/**
 * Unit tests to test class Color.
 */
class ColorTest extends TestCase
{
    /**
     * Mock a MetaModel.
     *
     * @param string   $language         The language.
     *
     * @param string   $fallbackLanguage The fallback language.
     *
     * @return \MetaModels\IMetaModel
     */
    protected function mockMetaModel($language, $fallbackLanguage)
    {
        $metaModel = $this->getMockForAbstractClass('MetaModels\IMetaModel');

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

        return $metaModel;
    }

    /**
     * Mock the database connection.
     *
     * @return \PHPUnit_Framework_MockObject_MockObject|Connection
     */
    private function mockConnection()
    {
        return $this->getMockBuilder(Connection::class)
            ->disableOriginalConstructor()
            ->getMock();
    }

    /**
     * Mock the table manipulator.
     *
     * @param Connection $connection The database connection mock.
     *
     * @return TableManipulator|\PHPUnit_Framework_MockObject_MockObject
     */
    private function mockTableManipulator(Connection $connection)
    {
        return $this->getMockBuilder(TableManipulator::class)
            ->setConstructorArgs([$connection, []])
            ->getMock();
    }

    /**
     * Test that the attribute can be instantiated.
     *
     * @return void
     */
    public function testInstantiation()
    {
        $connection  = $this->mockConnection();
        $manipulator = $this->mockTableManipulator($connection);

        $text = new Color($this->mockMetaModel('en', 'en'), [], $connection, $manipulator);
        $this->assertInstanceOf(Color::class, $text);
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
        $return = array();
        foreach ($data as $item) {
            $ids[] = $item['id'];
            $return[] = (object) $item;
        }

        $metaModel    = $this->mockMetaModel('en', 'en');
        $connection   = $this->mockConnection();
        $manipulator  = $this->mockTableManipulator($connection);
        $queryBuilder = $this->getMockBuilder(QueryBuilder::class)
            ->disableOriginalConstructor()
            ->getMock();

        $connection
            ->expects($this->once())
            ->method('createQueryBuilder')
            ->willReturn($queryBuilder);

        $statement = $this->getMockBuilder(Statement::class)->getMock();
        $statement->method('fetch')->willReturnOnConsecutiveCalls(...$return);

        $queryBuilder
            ->expects($this->once())
            ->method('execute')
            ->willReturn($statement);

        foreach (['select', 'addSelect', 'from', 'setParameter', 'where'] as $method) {
            $queryBuilder
                ->method($method)
                ->willReturn($queryBuilder);
        }

        $color = new Color($metaModel, array('colname' => 'color'), $connection, $manipulator);

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
            $return[] = (object) $item;
        }

        $metaModel    = $this->mockMetaModel('en', 'en');
        $connection   = $this->mockConnection();
        $manipulator  = $this->mockTableManipulator($connection);
        $queryBuilder = $this->getMockBuilder(QueryBuilder::class)
            ->disableOriginalConstructor()
            ->getMock();

        $connection
            ->expects($this->once())
            ->method('createQueryBuilder')
            ->willReturn($queryBuilder);

        $statement = $this->getMockBuilder(Statement::class)->getMock();
        $statement->method('fetch')->willReturnOnConsecutiveCalls(...$return);

        $queryBuilder
            ->expects($this->once())
            ->method('execute')
            ->willReturn($statement);

        foreach (['select', 'addSelect', 'from', 'setParameter', 'where'] as $method) {
            $queryBuilder
                ->method($method)
                ->willReturn($queryBuilder);
        }

        $color = new Color($metaModel, array('colname' => 'color'), $connection, $manipulator);

        $this->assertEquals(array_reverse($expected), $color->sortIds($ids, 'DESC'));
    }
}
