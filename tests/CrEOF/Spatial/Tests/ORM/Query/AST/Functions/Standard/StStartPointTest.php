<?php
/**
 * This file is part of the doctrine spatial extension.
 *
 * PHP 7.4 | 8.0
 *
 * (c) Alexandre Tranchant <alexandre.tranchant@gmail.com> 2017 - 2021
 * (c) Longitude One 2020 - 2021
 * (c) 2015 Derek J. Lambert
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 */

namespace CrEOF\Spatial\Tests\ORM\Query\AST\Functions\Standard;

use CrEOF\Spatial\Exception\InvalidValueException;
use CrEOF\Spatial\Exception\UnsupportedPlatformException;
use CrEOF\Spatial\Tests\Helper\LineStringHelperTrait;
use CrEOF\Spatial\Tests\OrmTestCase;
use Doctrine\DBAL\DBALException;
use Doctrine\ORM\ORMException;

/**
 * ST_StartPoint DQL function tests.
 *
 * @author  Derek J. Lambert <dlambert@dereklambert.com>
 * @author  Alexandre Tranchant <alexandre.tranchant@gmail.com>
 * @license https://dlambert.mit-license.org MIT
 *
 * @group dql
 *
 * @internal
 * @coversDefaultClass
 */
class StStartPointTest extends OrmTestCase
{
    use LineStringHelperTrait;

    /**
     * Setup the function type test.
     *
     * @throws DBALException                when connection failed
     * @throws ORMException                 when cache is not set
     * @throws UnsupportedPlatformException when platform is unsupported
     */
    protected function setUp(): void
    {
        $this->usesEntity(self::LINESTRING_ENTITY);
        $this->supportsPlatform('postgresql');
        $this->supportsPlatform('mysql');

        parent::setUp();
    }

    /**
     * Test a DQL containing function to test in the select.
     *
     * @throws DBALException                when connection failed
     * @throws ORMException                 when cache is not set
     * @throws UnsupportedPlatformException when platform is unsupported
     * @throws InvalidValueException        when geometries are not valid
     *
     * @group geometry
     */
    public function testStStartPointSelect()
    {
        $this->createStraightLineString();
        $this->getEntityManager()->flush();
        $this->getEntityManager()->clear();

        $query = $this->getEntityManager()->createQuery(
            'SELECT ST_AsText(ST_StartPoint(l.lineString)) FROM CrEOF\Spatial\Tests\Fixtures\LineStringEntity l'
        );

        $result = $query->getResult();

        static::assertEquals('POINT(0 0)', $result[0][1]);
    }

    /**
     * Test a DQL containing function to test in the predicate.
     *
     * @throws DBALException                when connection failed
     * @throws ORMException                 when cache is not set
     * @throws UnsupportedPlatformException when platform is unsupported
     * @throws InvalidValueException        when geometries are not valid
     *
     * @group geometry
     */
    public function testStStartPointWhereCompareLineString()
    {
        $this->createStraightLineString();
        $angularLineString = $this->createAngularLineString();
        $this->getEntityManager()->flush();
        $this->getEntityManager()->clear();

        $query = $this->getEntityManager()->createQuery(
            // phpcs:disable Generic.Files.LineLength.MaxExceeded
            'SELECT l FROM CrEOF\Spatial\Tests\Fixtures\LineStringEntity l WHERE ST_StartPoint(l.lineString) = ST_StartPoint(ST_GeomFromText(:p1))'
            // phpcs:enable
        );

        $query->setParameter('p1', 'LINESTRING(3 3, 4 15, 5 22)', 'string');

        $result = $query->getResult();

        static::assertCount(1, $result);
        static::assertEquals($angularLineString, $result[0]);
    }

    /**
     * Test a DQL containing function to test in the predicate.
     *
     * @throws DBALException                when connection failed
     * @throws ORMException                 when cache is not set
     * @throws UnsupportedPlatformException when platform is unsupported
     * @throws InvalidValueException        when geometries are not valid
     *
     * @group geometry
     */
    public function testStStartPointWhereComparePoint()
    {
        $straightLineString = $this->createStraightLineString();
        $this->createAngularLineString();

        $this->getEntityManager()->flush();
        $this->getEntityManager()->clear();

        $query = $this->getEntityManager()->createQuery(
            // phpcs:disable Generic.Files.LineLength.MaxExceeded
            'SELECT l FROM CrEOF\Spatial\Tests\Fixtures\LineStringEntity l WHERE ST_StartPoint(l.lineString) = ST_GeomFromText(:p1)'
            // phpcs:enable
        );

        $query->setParameter('p1', 'POINT(0 0)', 'string');

        $result = $query->getResult();

        static::assertCount(1, $result);
        static::assertEquals($straightLineString, $result[0]);
    }
}
