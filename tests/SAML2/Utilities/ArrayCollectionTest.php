<?php

declare(strict_types=1);

namespace SAML2\Utilities;

use PHPUnit\Framework\TestCase;
use SAML2\Utilities\ArrayCollection;
use SAML2\Exception\RuntimeException;

/**
 * @covers \SAML2\Utilities\ArrayCollection
 * @package simplesamlphp/saml2
 */
final class ArrayCollectionTest extends TestCase
{
    /**
     * @return void
     */
    public function testConstructGetAddSet(): void
    {
        $arc = new ArrayCollection(['aap', 'aap', 'noot']);

        $this->assertEquals($arc->get(0), 'aap');
        $this->assertEquals($arc->get(1), 'aap');
        $this->assertEquals($arc->get(2), 'noot');
        $this->assertNull($arc->get(3));

        $arc->add('mies');
        $this->assertEquals($arc->get(0), 'aap');
        $this->assertEquals($arc->get(1), 'aap');
        $this->assertEquals($arc->get(2), 'noot');
        $this->assertEquals($arc->get(3), 'mies');

        $arc->set(1, 'mies');
        $this->assertEquals($arc->get(0), 'aap');
        $this->assertEquals($arc->get(1), 'mies');
        $this->assertEquals($arc->get(2), 'noot');
        $this->assertEquals($arc->get(3), 'mies');
    }


    /**
     * @return void
     */
    public function testRemove(): void
    {
        $arc = new ArrayCollection(['aap', 'aap', 'noot', 'mies']);

        $arc->remove('noot');
        $this->assertEquals($arc->get(0), 'aap');
        $this->assertEquals($arc->get(1), 'aap');
        $this->assertEquals($arc->get(2), null);
        $this->assertEquals($arc->get(3), 'mies');

        $arc->remove('wim');
        $this->assertEquals($arc->get(0), 'aap');
        $this->assertEquals($arc->get(1), 'aap');
        $this->assertEquals($arc->get(2), null);
        $this->assertEquals($arc->get(3), 'mies');

        $arc->remove('aap');
        $this->assertEquals($arc->get(0), null);
        $this->assertEquals($arc->get(1), 'aap');
        $this->assertEquals($arc->get(2), null);
        $this->assertEquals($arc->get(3), 'mies');

        $arc->remove('aap');
        $this->assertEquals($arc->get(0), null);
        $this->assertEquals($arc->get(1), null);
        $this->assertEquals($arc->get(2), null);
        $this->assertEquals($arc->get(3), 'mies');
    }


    /**
     * @return void
     */
    public function testFirstLastCount(): void
    {
        $arc = new ArrayCollection(['aap', 'aap', 'noot', 'mies']);

        $this->assertEquals($arc->first(), 'aap');
        $this->assertEquals($arc->last(), 'mies');
        $this->assertEquals($arc->count(), 4);
    }


    /**
     * @return void
     */
    public function testOffset(): void
    {
        $arc = new ArrayCollection(['aap', 'aap', 'noot', 'mies']);

        $this->assertTrue($arc->offsetExists(0));
        $this->assertTrue($arc->offsetExists(1));
        $this->assertFalse($arc->offsetExists(4));

        $this->assertEquals($arc->offsetGet(0), 'aap');
        $this->assertEquals($arc->offsetGet(2), 'noot');

        $arc->offsetSet(2, 'zus');
        $this->assertEquals($arc->offsetGet(2), 'zus');

        $arc->offsetUnset(0);
        $this->assertFalse($arc->offsetExists(0));
        $this->assertTrue($arc->offsetExists(1));
    }


    /**
     * @return void
     */
    public function testOnlyElement(): void
    {
        $arc = new ArrayCollection(['aap']);
        $this->assertEquals($arc->getOnlyElement(), 'aap');
    }


    /**
     * @return void
     */
    public function testOnlyElementFail(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage(
            'SAML2\Utilities\ArrayCollection::getOnlyElement requires that the collection has exactly one element, "2" elements found'
        );
        $arc = new ArrayCollection(['aap', 'noot']);
        $arc->getOnlyElement();
    }


    /**
     * @return void
     */
    public function testGetiterator(): void
    {
        $arc = new ArrayCollection(['aap', 'noot']);
        $this->assertInstanceOf(\ArrayIterator::class, $arc->getIterator());
    }


    /**
     * @return void
     */
    public function testFilterMap(): void
    {
        $arc = new ArrayCollection(['aap', 'aap', 'noot', 'mies']);

        $filtered = $arc->filter(
            function ($i) {
                return $i != 'aap';
            }
        );
        $this->assertInstanceOf(ArrayCollection::class, $filtered);
        $this->assertEquals($filtered->get(0), null);
        $this->assertEquals($filtered->get(1), null);
        $this->assertEquals($filtered->get(2), 'noot');
        $this->assertEquals($filtered->get(3), 'mies');

        $mapped = $arc->map(
            function ($i) {
                return ucfirst($i);
            }
        );
        $this->assertInstanceOf(ArrayCollection::class, $mapped);
        $this->assertEquals($mapped->get(0), 'Aap');
        $this->assertEquals($mapped->get(1), 'Aap');
        $this->assertEquals($mapped->get(2), 'Noot');
        $this->assertEquals($mapped->get(3), 'Mies');

        // ensure original is not changed
        $this->assertEquals($arc->get(0), 'aap');
        $this->assertEquals($arc->get(1), 'aap');
        $this->assertEquals($arc->get(2), 'noot');
        $this->assertEquals($arc->get(3), 'mies');
    }
}
