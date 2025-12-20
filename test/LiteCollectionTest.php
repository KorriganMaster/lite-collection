<?php

namespace Test;

use Countable;
use Korriganmaster\LiteCollection\LiteCollection;
use Korriganmaster\LiteCollection\Storage\SqlliteStorage;
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
     * @var StorageInterface $storage
     */
    private StorageInterface $storage;

    /**
     * Setup method to initialize storage before each test.
     */
    public function setUp(): void
    {
        $this->storage = new SqlliteStorage();
    }

    /**
     * Test that LiteCollection implements Countable interface.
     */
    public function testCountableInterface()
    {
        $collection = new LiteCollection($this->storage);
        $this->assertInstanceOf(Countable::class, $collection);

        // Initially, the collection should be empty
        $this->assertCount(0, $collection);

        // Add some items to the storage
        $this->storage->insert(['id' => 1, 'name' => 'Item 1']);
        $this->storage->insert(['id' => 2, 'name' => 'Item 2']);

        // Now, the collection should have 2 items
        $this->assertCount(2, $collection);
    }

    /**
     * Test that LiteCollection implements ArrayAccess interface.
     */
    public function testArrayAccessInterface()
    {
        $collection = new LiteCollection($this->storage);

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
        $storage = new SqlliteStorage(StorageInterface::MODE_ASSOCIATIVE, 'id');
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
        $collection = new LiteCollection($this->storage);

        // Add some items to the storage
        $collection[] = ['id' => 1, 'name' => 'Item 1'];
        $collection[] = ['id' => 2, 'name' => 'Item 2'];
        $collection[] = ['id' => 3, 'name' => 'Item 3'];

        $names = [];
        foreach ($collection as $key => $item) {
            $names[$key] = $item['name'];
        }

        $this->assertEquals([0 => 'Item 1', 1 => 'Item 2', 2 => 'Item 3'], $names);
    }

    /**
     * Test iterating over LiteCollection in associative mode.
     */
    public function testLoopingOverCollectionAssoc()
    {
        $storage = new SqlliteStorage(StorageInterface::MODE_ASSOCIATIVE, 'custom_id');
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
}