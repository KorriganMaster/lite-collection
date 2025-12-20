<?php

namespace Korriganmaster\LiteCollection;

use ArrayAccess;
use Countable;
use Iterator;
use IteratorAggregate;
use Korriganmaster\LiteCollection\Storage\StorageInterface;
use Traversable;

/**
 * Class LiteCollection
 *
 * A lightweight collection class that implements Countable interface.
 *
 * @implements ArrayAccess<int, mixed>
 * @implements IteratorAggregate<int, mixed>
 *  
 * @package Korriganmaster\LiteCollection
 */
class LiteCollection implements Countable, ArrayAccess, IteratorAggregate
{
    /**
     * @param StorageInterface $storage
     */
    private StorageInterface $storage;

    /**
     * LiteCollection constructor.
     *
     * @param StorageInterface $storage
     */
    public function __construct(StorageInterface $storage)
    {
        $this->storage = $storage;
    }

    /**
     * Get the number of items in the collection.
     *
     * @return int
     */
    public function count(): int
    {
        return count($this->storage);
    }

    /**
     * Retrieve an item by its offset.
     *
     * @param int $offset
     * @return mixed
     */
    public function offsetGet($offset): mixed
    {
        return $this->storage->findById($offset);
    }

    /**
     * Set an item at the specified offset.
     *
     * @param int $offset
     * @param mixed $value
     */
    public function offsetSet($offset = null, $value = null): void
    {
        if (is_null($offset)) {
            $this->storage->insert($value);
        } else {
            $this->storage->update($offset, $value);
        }
    }

    /**
     * Unset an item at the specified offset.
     *
     * @param int $offset
     */
    public function offsetUnset($offset): void
    {
        $this->storage->delete($offset);
    }  

    /**
     * Check if an item exists at the specified offset.
     *
     * @param int $offset
     * @return bool
     */
    public function offsetExists($offset): bool
    {
        return $this->storage->exists($offset);
    }

    /**
     * Retrieve an external iterator.
     *
     * @return Iterator
     */
    public function getIterator(): Iterator
    {
        return $this->storage;
    }
}