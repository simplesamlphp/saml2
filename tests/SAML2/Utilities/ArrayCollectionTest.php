<?php

declare(strict_types=1);

namespace SAML2\Tests\Utilities;

use SAML2\Utilities\ArrayCollection;
use SAML2\Exception\RuntimeException;

class ArrayCollectionTest extends \PHPUnit\Framework\TestCase
{

    public function test_construct_get_add_set()
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

    public function test_remove()
    {
        $arc = new ArrayCollection(['aap', 'aap', 'noot', 'mies']);

        $removed = $arc->remove('noot');
        $this->assertEquals('noot', $removed);
        $this->assertEquals($arc->get(0), 'aap');
        $this->assertEquals($arc->get(1), 'aap');
        $this->assertEquals($arc->get(2), null);
        $this->assertEquals($arc->get(3), 'mies');

        $removed = $arc->remove('wim');
        $this->assertFalse($removed);
        $this->assertEquals($arc->get(0), 'aap');
        $this->assertEquals($arc->get(1), 'aap');
        $this->assertEquals($arc->get(2), null);
        $this->assertEquals($arc->get(3), 'mies');

        $removed = $arc->remove('aap');
        $this->assertEquals('aap', $removed);
        $this->assertEquals($arc->get(0), null);
        $this->assertEquals($arc->get(1), 'aap');
        $this->assertEquals($arc->get(2), null);
        $this->assertEquals($arc->get(3), 'mies');

        $removed = $arc->remove('aap');
        $this->assertEquals('aap', $removed);
        $this->assertEquals($arc->get(0), null);
        $this->assertEquals($arc->get(1), null);
        $this->assertEquals($arc->get(2), null);
        $this->assertEquals($arc->get(3), 'mies');
    }

    public function test_first_last_count()
    {
        $arc = new ArrayCollection(['aap', 'aap', 'noot', 'mies']);

        $this->assertEquals($arc->first(), 'aap');
        $this->assertEquals($arc->last(), 'mies');
        $this->assertEquals($arc->count(), 4);
    }

    public function test_offset()
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

    public function test_onlyelement()
    {
        $arc = new ArrayCollection(['aap']);
        $this->assertEquals($arc->getOnlyElement(), 'aap');
    }

    public function test_onlyelement_fail()
    {
        $arc = new ArrayCollection(['aap', 'noot']);
        $this->expectException(RuntimeException::class, 'SAML2\Utilities\ArrayCollection::SAML2\Utilities\ArrayCollection::getOnlyElement requires that the collection has exactly one element, "2" elements found');
        $arc->getOnlyElement();
    }

    public function test_getiterator()
    {
        $arc = new ArrayCollection(['aap', 'noot']);
        $this->assertInstanceOf(\ArrayIterator::class, $arc->getIterator());
    }

    public function test_filter_map()
    {
        $arc = new ArrayCollection(['aap', 'aap', 'noot', 'mies']);

        $filtered = $arc->filter(function ($i) { return $i != 'aap'; });
        $this->assertInstanceOf(ArrayCollection::class, $filtered);
        $this->assertEquals($filtered->get(0), null);
        $this->assertEquals($filtered->get(1), null);
        $this->assertEquals($filtered->get(2), 'noot');
        $this->assertEquals($filtered->get(3), 'mies');

        $mapped = $arc->map(function ($i) { return ucfirst($i); });
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
