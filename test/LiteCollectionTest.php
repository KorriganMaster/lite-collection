<?php

namespace Test;

use Countable;
use Korriganmaster\LiteCollection\LiteCollection;
use Korriganmaster\LiteCollection\Storage\SqliteStorage;
use Korriganmaster\LiteCollection\Storage\StorageInterface;
use PHPUnit\Framework\TestCase;

/**
 * Class LiteCollectionTest
 *
 * This class contains unit tests for the LiteCollection class,
 * ensuring it correctly implements Countable and ArrayAccess interfaces,
 * and behaves as expected when iterating over its items.
 * 
 * @covers \Korriganmaster\LiteCollection\LiteCollection
 */
class LiteCollectionTest extends TestCase
{

    /**
     * Test that LiteCollection implements Countable interface.
     */
    public function testCountableInterface()
    {
        $collection = new LiteCollection(new SqliteStorage());
        $this->assertInstanceOf(Countable::class, $collection);

        // Initially, the collection should be empty
        $this->assertCount(0, $collection);

        // Add some items to the storage
        $collection[] = ['id' => 1, 'name' => 'Item 1'];
        $collection[] = ['id' => 2, 'name' => 'Item 2'];

        // Now, the collection should have 2 items
        $this->assertCount(2, $collection);
    }

    /**
     * Test that LiteCollection implements ArrayAccess interface.
     */
    public function testArrayAccessInterface()
    {
        $collection = new LiteCollection(new SqliteStorage());

        // Add some items to the storage
        $collection[] = ['id' => 1, 'name' => 'Item 1'];
        $collection[] = ['id' => 2, 'name' => 'Item 2'];

        // Test array access
        $this->assertEquals(['id' => 1, 'name' => 'Item 1'], $collection[0]);
        $this->assertEquals(['id' => 2, 'name' => 'Item 2'], $collection[1]);

        // Test setting an item
        $collection[1] = ['id' => 2, 'name' => 'Updated Item 2'];
        $this->assertEquals(['id' => 2, 'name' => 'Updated Item 2'], $collection[1]);

        // Test unsetting an item
        unset($collection[0]);
        $this->assertCount(1, $collection);
        $this->assertEquals(['id' => 2, 'name' => 'Updated Item 2'], $collection[0]);
    }

    /**
     * Test that LiteCollection implements ArrayAccess interface in associative mode.
     */
    public function testArrayAccessInterfaceAssoc()
    {
        $storage = new SqliteStorage(StorageInterface::MODE_ASSOCIATIVE, 'id');
        $collection = new LiteCollection($storage);

        // Add some items to the storage
        $collection[] = ['id' => 1, 'name' => 'Item 1'];
        $collection[] = ['id' => 2, 'name' => 'Item 2'];

        // Test array access
        $this->assertEquals(['id' => 1, 'name' => 'Item 1'], $collection[1]);
        $this->assertEquals(['id' => 2, 'name' => 'Item 2'], $collection[2]);

        // Test setting an item
        $collection[2] = ['id' => 2, 'name' => 'Updated Item 2'];
        $this->assertEquals(['id' => 2, 'name' => 'Updated Item 2'], $collection[2]);

        // Test unsetting an item
        unset($collection[1]);
        $this->assertCount(1, $collection);
        $this->assertEquals(['id' => 2, 'name' => 'Updated Item 2'], $collection[2]);
    }

    /**
     * Test iterating over LiteCollection.
     */
    public function testLoopingOverCollection()
    {
        $collection = new LiteCollection(new SqliteStorage());

        // Add some items to the storage
        $collection[] = ['id' => 1, 'name' => 'Item 1'];
        $collection[] = ['id' => 2, 'name' => 'Item 2'];
        $collection[] = ['id' => 3, 'name' => 'Item 3'];

        $names = [];
        foreach ($collection as $key => $item) {
            $names[$key] = $item['name'];
        }

        $this->assertEquals([0 => 'Item 1', 1 => 'Item 2', 2 => 'Item 3'], $names);

        // Test with object items
        $collection = new LiteCollection(new SqliteStorage());
        $collection[] = (object)['id' => 1, 'name' => 'Item 1'];
        $collection[] = (object)['id' => 2, 'name' => 'Item 2'];
        $collection[] = (object)['id' => 3, 'name' => 'Item 3'];
        $names = [];
        foreach ($collection as $key => $item) {
            $names[$key] = $item->name;
        }
        $this->assertEquals([0 => 'Item 1', 1 => 'Item 2', 2 => 'Item 3'], $names);

    }

    /**
     * Test iterating over LiteCollection in associative mode.
     */
    public function testLoopingOverCollectionAssoc()
    {
        $storage = new SqliteStorage(StorageInterface::MODE_ASSOCIATIVE, 'custom_id');
        $collection = new LiteCollection($storage);

        // Add some items to the storage
        $collection[] = ['custom_id' => 1, 'name' => 'Item 1'];
        $collection[] = ['custom_id' => 2, 'name' => 'Item 2'];
        $collection[] = ['custom_id' => 3, 'name' => 'Item 3'];

        $names = [];
        foreach ($collection as $key => $item) {
            $names[$key] = $item['name'];
        }

        $this->assertEquals([1 => 'Item 1', 2 => 'Item 2', 3 => 'Item 3'], $names);
    }

    /**
     * Test offsetExists method.
     */
    public function testOffsetExists()
    {
        $collection = new LiteCollection(new SqliteStorage());
        
        // Add items
        $collection[] = ['id' => 1, 'name' => 'Item 1'];
        $collection[] = ['id' => 2, 'name' => 'Item 2'];
        
        // Test existing offsets
        $this->assertTrue(isset($collection[0]));
        $this->assertTrue(isset($collection[1]));
        
        // Test non-existing offset
        $this->assertFalse(isset($collection[2]));
        $this->assertFalse(isset($collection[99]));
    }

    /**
     * Test offsetExists in associative mode.
     */
    public function testOffsetExistsAssoc()
    {
        $storage = new SqliteStorage(StorageInterface::MODE_ASSOCIATIVE, 'id');
        $collection = new LiteCollection($storage);
        
        $collection[] = ['id' => 10, 'name' => 'Item 10'];
        $collection[] = ['id' => 20, 'name' => 'Item 20'];
        
        $this->assertTrue(isset($collection[10]));
        $this->assertTrue(isset($collection[20]));
        $this->assertFalse(isset($collection[0]));
        $this->assertFalse(isset($collection[15]));
    }

    /**
     * Test that storage is correctly injected via constructor.
     */
    public function testConstructorWithStorage()
    {
        $storage = new SqliteStorage();
        $collection = new LiteCollection($storage);
        
        $this->assertInstanceOf(LiteCollection::class, $collection);
        $this->assertCount(0, $collection);
    }

    /**
     * Test adding multiple items in sequence.
     */
    public function testAddingMultipleItems()
    {
        $collection = new LiteCollection(new SqliteStorage());
        
        for ($i = 1; $i <= 5; $i++) {
            $collection[] = ['id' => $i, 'name' => "Item $i"];
        }
        
        $this->assertCount(5, $collection);
        $this->assertEquals(['id' => 3, 'name' => 'Item 3'], $collection[2]);
    }

    /**
     * Test updating existing items.
     */
    public function testUpdatingItems()
    {
        $collection = new LiteCollection(new SqliteStorage());
        
        $collection[] = ['id' => 1, 'name' => 'Original'];
        $collection[0] = ['id' => 1, 'name' => 'Updated'];
        
        $this->assertEquals(['id' => 1, 'name' => 'Updated'], $collection[0]);
        $this->assertCount(1, $collection);
    }

    /**
     * Test deleting all items.
     */
    public function testDeletingAllItems()
    {
        $collection = new LiteCollection(new SqliteStorage());
        
        $collection[] = ['id' => 1, 'name' => 'Item 1'];
        $collection[] = ['id' => 2, 'name' => 'Item 2'];
        $collection[] = ['id' => 3, 'name' => 'Item 3'];
        
        $this->assertCount(3, $collection);
        
        unset($collection[0]);
        unset($collection[0]);
        unset($collection[0]);
        
        $this->assertCount(0, $collection);
    }

    /**
     * Test getIterator returns an Iterator.
     */
    public function testGetIteratorReturnsIterator()
    {
        $collection = new LiteCollection(new SqliteStorage());
        
        $iterator = $collection->getIterator();
        
        $this->assertInstanceOf(\Iterator::class, $iterator);
    }

    /**
     * Test empty collection iteration.
     */
    public function testEmptyCollectionIteration()
    {
        $collection = new LiteCollection(new SqliteStorage());
        
        $count = 0;
        foreach ($collection as $item) {
            $count++;
        }
        
        $this->assertEquals(0, $count);
    }

    /**
     * Test collection with mixed data types.
     */
    public function testMixedDataTypes()
    {
        $collection = new LiteCollection(new SqliteStorage());
        
        $collection[] = ['type' => 'array', 'value' => [1, 2, 3]];
        $collection[] = (object)['type' => 'object', 'value' => 'test'];
        $collection[] = ['type' => 'string', 'value' => 'hello'];
        
        $this->assertCount(3, $collection);
        $this->assertIsArray($collection[0]);
        $this->assertIsObject($collection[1]);
        $this->assertIsArray($collection[2]);
    }

    /**
     * Test sequential access after deletion.
     */
    public function testSequentialAccessAfterDeletion()
    {
        $collection = new LiteCollection(new SqliteStorage());
        
        $collection[] = ['id' => 1, 'name' => 'Item 1'];
        $collection[] = ['id' => 2, 'name' => 'Item 2'];
        $collection[] = ['id' => 3, 'name' => 'Item 3'];
        
        unset($collection[1]);
        
        $this->assertCount(2, $collection);
        $this->assertEquals(['id' => 1, 'name' => 'Item 1'], $collection[0]);
        $this->assertEquals(['id' => 3, 'name' => 'Item 3'], $collection[1]);
    }
}