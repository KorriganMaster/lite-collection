<?php

namespace Test;

use Korriganmaster\LiteCollection\Storage\SqliteStorage;
use Korriganmaster\LiteCollection\Storage\StorageInterface;
use PHPUnit\Framework\TestCase;

/**
 * Class SqliteStorageTest
 * This class contains unit tests for the SqliteStorage class,
 * ensuring it correctly implements StorageInterface and behaves as expected.
 * 
 * @covers \Korriganmaster\LiteCollection\Storage\SqliteStorage
 */
class SqliteStorageTest extends TestCase
{
    /**
     * @covers \Korriganmaster\LiteCollection\Storage\SqliteStorage::__construct
     * @covers \Korriganmaster\LiteCollection\Storage\SqliteStorage::__destruct
     */
    public function testCreateAndDestructStorage()
    {
        $storage = new SqliteStorage();
        $this->assertInstanceOf(StorageInterface::class, $storage);
        unset($storage);

        $storage = new SqliteStorage(StorageInterface::MODE_NORMAL, 'id', 'test_db.sqlite');
        $this->assertFileExists('test_db.sqlite');
        unset($storage);
        $this->assertFileNotExists('test_db.sqlite');
    }

    /**
     * @covers \Korriganmaster\LiteCollection\Storage\SqliteStorage::count
     */
    public function testCountItemsInEmptyStorage()
    {
        $storage = new SqliteStorage();
        $this->assertEquals(0, count($storage));
        unset($storage);
    }

    /**
     * @covers \Korriganmaster\LiteCollection\Storage\SqliteStorage::insert
     * @covers \Korriganmaster\LiteCollection\Storage\SqliteStorage::findById
     */
    public function testInsertAndRetrieveItem()
    {
        $storage = new SqliteStorage();
        $this->assertEquals(0, count($storage));

        $item = ['id' => 1, 'name' => 'Test Item'];
        $storage->insert($item);
        $this->assertEquals(1, count($storage));

        $retrievedItem = $storage->findById(0);
        $this->assertEquals($item, $retrievedItem);

        unset($storage);
    }

    /**
     * @covers \Korriganmaster\LiteCollection\Storage\SqliteStorage::findById
     */
    public function testRetrieveNonExistentItem()
    {
        $storage = new SqliteStorage();
        $this->assertEquals(0, count($storage));

        $retrievedItem = $storage->findById(0);
        $this->assertNull($retrievedItem);

        unset($storage);
    }

    /**
     * @covers \Korriganmaster\LiteCollection\Storage\SqliteStorage::insert
     * @covers \Korriganmaster\LiteCollection\Storage\SqliteStorage::count
     * @covers \Korriganmaster\LiteCollection\Storage\SqliteStorage::rewind
     * @covers \Korriganmaster\LiteCollection\Storage\SqliteStorage::current
     * @covers \Korriganmaster\LiteCollection\Storage\SqliteStorage::key
     * @covers \Korriganmaster\LiteCollection\Storage\SqliteStorage::next
     * @covers \Korriganmaster\LiteCollection\Storage\SqliteStorage::valid
     */
    public function testInsertAndLoopItems()
    {
        $storage = new SqliteStorage();
        $this->assertEquals(0, count($storage));

        $items = [
            ['id' => 1, 'name' => 'Item 1'],
            ['id' => 2, 'name' => 'Item 2'],
            ['id' => 3, 'name' => 'Item 3'],
        ];

        foreach ($items as $item) {
            $storage->insert($item);
        }
        $this->assertEquals(3, count($storage));

        $retrievedItems = [];
        foreach ($storage as $item) {
            $retrievedItems[] = $item;
        }
        $this->assertEquals($items, $retrievedItems);

        unset($storage);
    }

    /**
     * @covers \Korriganmaster\LiteCollection\Storage\SqliteStorage::insert
     * @covers \Korriganmaster\LiteCollection\Storage\SqliteStorage::count
     * @covers \Korriganmaster\LiteCollection\Storage\SqliteStorage::rewind
     * @covers \Korriganmaster\LiteCollection\Storage\SqliteStorage::current
     * @covers \Korriganmaster\LiteCollection\Storage\SqliteStorage::key
     * @covers \Korriganmaster\LiteCollection\Storage\SqliteStorage::next
     * @covers \Korriganmaster\LiteCollection\Storage\SqliteStorage::valid
     */
    public function testInsertAndLoopItemsAssoc()
    {
        $storage = new SqliteStorage(StorageInterface::MODE_ASSOCIATIVE, 'custom_id');
        $this->assertEquals(0, count($storage));

        $items = [
            ['custom_id' => 1, 'name' => 'Item 1'],
            ['custom_id' => 2, 'name' => 'Item 2'],
            ['custom_id' => 3, 'name' => 'Item 3'],
        ];

        foreach ($items as $item) {
            $storage->insert($item);
        }
        $this->assertEquals(3, count($storage));

        $retrievedItems = [];
        foreach ($storage as $key => $item) {
            $this->assertEquals($item['custom_id'], $key);
            $retrievedItems[] = $item;
        }
        $this->assertEquals($items, $retrievedItems);

        unset($storage);
    }

    /**
     * @covers \Korriganmaster\LiteCollection\Storage\SqliteStorage::insert
     * @covers \Korriganmaster\LiteCollection\Storage\SqliteStorage::exists
     */
    public function testExistsMethod()
    {
        $storage = new SqliteStorage();
        $item = ['id' => 1, 'name' => 'Test Item'];
        $storage->insert($item);

        $this->assertTrue($storage->exists(0));
        $this->assertFalse($storage->exists(1));

        unset($storage);
    }

    /**
     * @covers \Korriganmaster\LiteCollection\Storage\SqliteStorage::insert
     * @covers \Korriganmaster\LiteCollection\Storage\SqliteStorage::exists
     */
    public function testExistsMethodAssoc()
    {
        $storage = new SqliteStorage(StorageInterface::MODE_ASSOCIATIVE, 'custom_id');
        $item = ['custom_id' => 1, 'name' => 'Test Item'];
        $storage->insert($item);

        $this->assertTrue($storage->exists(1));
        $this->assertFalse($storage->exists(2));

        unset($storage);
    }

    /**
     * @covers \Korriganmaster\LiteCollection\Storage\SqliteStorage::insert
     * @covers \Korriganmaster\LiteCollection\Storage\SqliteStorage::update
     * @covers \Korriganmaster\LiteCollection\Storage\SqliteStorage::delete
     * @covers \Korriganmaster\LiteCollection\Storage\SqliteStorage::findById
     * @covers \Korriganmaster\LiteCollection\Storage\SqliteStorage::exists
     */
    public function testUpdateAndDeleteMethods()
    {
        $storage = new SqliteStorage();
        $item = ['id' => 1, 'name' => 'Original Name'];
        $storage->insert($item);

        // Ensure item exists
        $this->assertTrue($storage->exists(0));
        $retrievedItem = $storage->findById(0);
        $this->assertEquals($item, $retrievedItem);

        // Update the item
        $updatedItem = ['id' => 1, 'name' => 'Updated Name'];
        $storage->update(0, $updatedItem);
        $retrievedItem = $storage->findById(0);
        $this->assertEquals($updatedItem, $retrievedItem);

        // Delete the item
        $storage->delete(0);
        $this->assertFalse($storage->exists(0));
        $retrievedItem = $storage->findById(0);
        $this->assertNull($retrievedItem);
    }

    /**
     * @covers \Korriganmaster\LiteCollection\Storage\SqliteStorage::insert
     * @covers \Korriganmaster\LiteCollection\Storage\SqliteStorage::update
     * @covers \Korriganmaster\LiteCollection\Storage\SqliteStorage::delete
     * @covers \Korriganmaster\LiteCollection\Storage\SqliteStorage::findById
     * @covers \Korriganmaster\LiteCollection\Storage\SqliteStorage::exists
     */
    public function testUpdateAndDeleteMethodsAssoc()
    {
        $storage = new SqliteStorage(StorageInterface::MODE_ASSOCIATIVE, 'custom_id');
        $item = ['custom_id' => 1, 'name' => 'Original Name'];
        $storage->insert($item);

        // Ensure item exists
        $this->assertTrue($storage->exists(1));
        $retrievedItem = $storage->findById(1);
        $this->assertEquals($item, $retrievedItem);

        // Update the item
        $updatedItem = ['custom_id' => 1, 'name' => 'Updated Name'];
        $storage->update(1, $updatedItem);
        $retrievedItem = $storage->findById(1);
        $this->assertEquals($updatedItem, $retrievedItem);
    
        // Delete the item
        $storage->delete(1);
        $this->assertFalse($storage->exists(1));
        $retrievedItem = $storage->findById(1);
        $this->assertNull($retrievedItem);
    }

    /**
     * @covers \Korriganmaster\LiteCollection\Storage\SqliteStorage::insert
     * @covers \Korriganmaster\LiteCollection\Storage\SqliteStorage::delete
     * @covers \Korriganmaster\LiteCollection\Storage\SqliteStorage::findById
     */
    public function testDeleteReindexesInNormalMode()
    {
        $storage = new SqliteStorage();
        $items = [
            ['id' => 1, 'name' => 'Item 1'],
            ['id' => 2, 'name' => 'Item 2'],
            ['id' => 3, 'name' => 'Item 3'],
        ];

        foreach ($items as $item) {
            $storage->insert($item);
        }

        // Delete middle item
        $storage->delete(1);
        
        // Item at index 2 should now be at index 1
        $retrievedItem = $storage->findById(1);
        $this->assertEquals($items[2], $retrievedItem);
        
        // Original index 2 should not exist
        $this->assertNull($storage->findById(2));
        
        unset($storage);
    }

    /**
     * @covers \Korriganmaster\LiteCollection\Storage\SqliteStorage::insert
     * @covers \Korriganmaster\LiteCollection\Storage\SqliteStorage::delete
     * @covers \Korriganmaster\LiteCollection\Storage\SqliteStorage::findById
     */
    public function testDeleteDoesNotReindexInAssociativeMode()
    {
        $storage = new SqliteStorage(StorageInterface::MODE_ASSOCIATIVE, 'custom_id');
        $items = [
            ['custom_id' => 10, 'name' => 'Item 10'],
            ['custom_id' => 20, 'name' => 'Item 20'],
            ['custom_id' => 30, 'name' => 'Item 30'],
        ];

        foreach ($items as $item) {
            $storage->insert($item);
        }

        // Delete middle item
        $storage->delete(20);
        
        // Other items should remain at their original IDs
        $this->assertEquals($items[0], $storage->findById(10));
        $this->assertNull($storage->findById(20));
        $this->assertEquals($items[2], $storage->findById(30));
        
        unset($storage);
    }

    /**
     * @covers \Korriganmaster\LiteCollection\Storage\SqliteStorage::insert
     * @covers \Korriganmaster\LiteCollection\Storage\SqliteStorage::count
     */
    public function testInsertMultipleItems()
    {
        $storage = new SqliteStorage();
        
        for ($i = 0; $i < 100; $i++) {
            $storage->insert(['id' => $i, 'name' => "Item $i"]);
        }
        
        $this->assertEquals(100, count($storage));
        
        unset($storage);
    }

    /**
     * @covers \Korriganmaster\LiteCollection\Storage\SqliteStorage::rewind
     * @covers \Korriganmaster\LiteCollection\Storage\SqliteStorage::valid
     */
    public function testRewindMultipleTimes()
    {
        $storage = new SqliteStorage();
        $items = [
            ['id' => 1, 'name' => 'Item 1'],
            ['id' => 2, 'name' => 'Item 2'],
        ];

        foreach ($items as $item) {
            $storage->insert($item);
        }

        // First iteration
        $count1 = 0;
        foreach ($storage as $item) {
            $count1++;
        }

        // Second iteration (tests rewind)
        $count2 = 0;
        foreach ($storage as $item) {
            $count2++;
        }

        $this->assertEquals($count1, $count2);
        $this->assertEquals(2, $count1);
        
        unset($storage);
    }

    /**
     * @covers \Korriganmaster\LiteCollection\Storage\SqliteStorage::insert
     * @covers \Korriganmaster\LiteCollection\Storage\SqliteStorage::findById
     */
    public function testInsertComplexDataStructures()
    {
        $storage = new SqliteStorage();
        $complexItem = [
            'id' => 1,
            'nested' => [
                'level1' => [
                    'level2' => 'deep value'
                ]
            ],
            'objects' => new \stdClass(),
        ];
        
        $storage->insert($complexItem);
        $retrievedItem = $storage->findById(0);
        
        $this->assertEquals($complexItem['nested']['level1']['level2'], $retrievedItem['nested']['level1']['level2']);
        
        unset($storage);
    }

    /**
     * @covers \Korriganmaster\LiteCollection\Storage\SqliteStorage::key
     */
    public function testKeyReturnsCorrectValueInNormalMode()
    {
        $storage = new SqliteStorage();
        $storage->insert(['name' => 'Item 1']);
        $storage->insert(['name' => 'Item 2']);
        
        $keys = [];
        foreach ($storage as $key => $item) {
            $keys[] = $key;
        }
        
        $this->assertEquals([0, 1], $keys);
        
        unset($storage);
    }

    /**
     * @covers \Korriganmaster\LiteCollection\Storage\SqliteStorage::insert
     * @covers \Korriganmaster\LiteCollection\Storage\SqliteStorage::update
     * @covers \Korriganmaster\LiteCollection\Storage\SqliteStorage::delete
     */
    public function testMultipleOperationsSequence()
    {
        $storage = new SqliteStorage();
        
        // Insert
        $storage->insert(['name' => 'Item 1']);
        $storage->insert(['name' => 'Item 2']);
        $storage->insert(['name' => 'Item 3']);
        $this->assertEquals(3, count($storage));
        
        // Update
        $storage->update(1, ['name' => 'Updated Item 2']);
        $this->assertEquals('Updated Item 2', $storage->findById(1)['name']);
        
        // Delete
        $storage->delete(0);
        $this->assertEquals(2, count($storage));
        
        unset($storage);
    }

    /**
     * @covers \Korriganmaster\LiteCollection\Storage\SqliteStorage::insert
     * @covers \Korriganmaster\LiteCollection\Storage\SqliteStorage::findById
     */
    public function testInsertArrayAccessObject()
    {
        $storage = new SqliteStorage(StorageInterface::MODE_ASSOCIATIVE, 'id');
        $item = new \ArrayObject(['id' => 5, 'name' => 'ArrayAccess Item']);
        
        $storage->insert($item);
        $retrievedItem = $storage->findById(5);
        
        $this->assertEquals(5, $retrievedItem['id']);
        $this->assertEquals('ArrayAccess Item', $retrievedItem['name']);
        
        unset($storage);
    }

    /**
     * @covers \Korriganmaster\LiteCollection\Storage\SqliteStorage::current
     * @covers \Korriganmaster\LiteCollection\Storage\SqliteStorage::valid
     */
    public function testIteratorOnEmptyStorage()
    {
        $storage = new SqliteStorage();
        
        $count = 0;
        foreach ($storage as $item) {
            $count++;
        }
        
        $this->assertEquals(0, $count);
        
        unset($storage);
    }
}