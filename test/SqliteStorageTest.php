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
        $this->assertFileDoesNotExist('test_db.sqlite');
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
}