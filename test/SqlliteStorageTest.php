<?php

namespace Test;

use Korriganmaster\LiteCollection\Storage\SqlliteStorage;
use Korriganmaster\LiteCollection\Storage\StorageInterface;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Korriganmaster\LiteCollection\Storage\SqlliteStorage
 */
class SqlliteStorageTest extends TestCase
{
    /**
     * @covers \Korriganmaster\LiteCollection\Storage\SqlliteStorage::__construct
     * @covers \Korriganmaster\LiteCollection\Storage\SqlliteStorage::__destruct
     */
    public function testCreateAndDestructStorage()
    {
        $storage = new SqlliteStorage();
        $this->assertInstanceOf(StorageInterface::class, $storage);
        unset($storage);

        $storage = new SqlliteStorage('test_db.sqlite');
        $this->assertFileExists('test_db.sqlite');
        unset($storage);
        $this->assertFileDoesNotExist('test_db.sqlite');
    }

    /**
     * @covers \Korriganmaster\LiteCollection\Storage\SqlliteStorage::count
     */
    public function testCountItemsInEmptyStorage()
    {
        $storage = new SqlliteStorage();
        $this->assertEquals(0, count($storage));
        unset($storage);
    }

    /**
     * @covers \Korriganmaster\LiteCollection\Storage\SqlliteStorage::insert
     * @covers \Korriganmaster\LiteCollection\Storage\SqlliteStorage::findById
     */
    public function testInsertAndRetrieveItem()
    {
        $storage = new SqlliteStorage();
        $this->assertEquals(0, count($storage));

        $item = ['id' => 1, 'name' => 'Test Item'];
        $storage->insert($item);
        $this->assertEquals(1, count($storage));

        $retrievedItem = $storage->findById(1);
        $this->assertEquals($item, $retrievedItem);

        unset($storage);
    }

    /**
     * @covers \Korriganmaster\LiteCollection\Storage\SqlliteStorage::insert
     * @covers \Korriganmaster\LiteCollection\Storage\SqlliteStorage::count
     * @covers \Korriganmaster\LiteCollection\Storage\SqlliteStorage::rewind
     * @covers \Korriganmaster\LiteCollection\Storage\SqlliteStorage::current
     * @covers \Korriganmaster\LiteCollection\Storage\SqlliteStorage::key
     * @covers \Korriganmaster\LiteCollection\Storage\SqlliteStorage::next
     * @covers \Korriganmaster\LiteCollection\Storage\SqlliteStorage::valid
     */
    public function testInsertAndLoopItems()
    {
        $storage = new SqlliteStorage();
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
     * @covers \Korriganmaster\LiteCollection\Storage\SqlliteStorage::insert
     * @covers \Korriganmaster\LiteCollection\Storage\SqlliteStorage::count
     * @covers \Korriganmaster\LiteCollection\Storage\SqlliteStorage::rewind
     * @covers \Korriganmaster\LiteCollection\Storage\SqlliteStorage::current
     * @covers \Korriganmaster\LiteCollection\Storage\SqlliteStorage::key
     * @covers \Korriganmaster\LiteCollection\Storage\SqlliteStorage::next
     * @covers \Korriganmaster\LiteCollection\Storage\SqlliteStorage::valid
     */
    public function testInsertAndLoopItemsAssoc()
    {
        $storage = new SqlliteStorage(':memory:', StorageInterface::MODE_ASSOCIATIVE, 'custom_id');
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
}