<?php

declare(strict_types=1);

namespace SimpleSAML\Test\SAML2\Utilities;

use ArrayIterator;
use PHPUnit\Framework\TestCase;
use SimpleSAML\SAML2\Exception\RuntimeException;
use SimpleSAML\SAML2\Utilities\ArrayCollection;

use function ucfirst;

/**
 * @covers \SimpleSAML\SAML2\Utilities\ArrayCollection
 * @package simplesamlphp/saml2
 */
final class ArrayCollectionTest extends TestCase
{
    /**
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
     */
    public function testFirstLastCount(): void
    {
        $arc = new ArrayCollection(['aap', 'aap', 'noot', 'mies']);

        $this->assertEquals($arc->first(), 'aap');
        $this->assertEquals($arc->last(), 'mies');
        $this->assertEquals($arc->count(), 4);
    }


    /**
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
     */
    public function testOnlyElement(): void
    {
        $arc = new ArrayCollection(['aap']);
        $this->assertEquals($arc->getOnlyElement(), 'aap');
    }


    /**
     */
    public function testOnlyElementFail(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage(sprintf(
            '%s::getOnlyElement requires that the collection has exactly one element, "2" elements found',
            ArrayCollection::class,
        ));
        $arc = new ArrayCollection(['aap', 'noot']);
        $arc->getOnlyElement();
    }


    /**
     */
    public function testGetiterator(): void
    {
        $arc = new ArrayCollection(['aap', 'noot']);
        $this->assertInstanceOf(ArrayIterator::class, $arc->getIterator());
    }


    /**
     */
    public function testFilterMap(): void
    {
        $arc = new ArrayCollection(['aap', 'aap', 'noot', 'mies']);

        $filtered = $arc->filter(
            function ($i) {
                return $i != 'aap';
            },
        );
        $this->assertInstanceOf(ArrayCollection::class, $filtered);
        $this->assertEquals($filtered->get(0), null);
        $this->assertEquals($filtered->get(1), null);
        $this->assertEquals($filtered->get(2), 'noot');
        $this->assertEquals($filtered->get(3), 'mies');

        $mapped = $arc->map(
            function ($i) {
                return ucfirst($i);
            },
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
