<?php 

namespace Korriganmaster\LiteCollection\Storage;

use Countable;
use Iterator;

/**
 * Interface StorageInterface
 * 
 * @extends Iterator<int, mixed>
 * 
 * @package Korriganmaster\LiteCollection\Storage
 */
interface StorageInterface extends Countable, Iterator
{
    // Storage modes
    const MODE_NORMAL = 1;
    const MODE_ASSOCIATIVE = 2;

    /**
     * StorageInterface constructor.
     */
    public function __construct();

    /**
     * Destructor to close the storage connection
     * and free resources
     * 
     * @return void
     */
    public function __destruct();

    /**
     * Insert an item into storage
     * 
     * @param mixed $item
     * @return void
     */
    public function insert(mixed $item): void;

    /**
     * Find an item by its ID
     * 
     * @param int $id
     * @return mixed
     */
    public function findById(int $id): mixed;

    /**
     * Check if an item exists by its ID
     * 
     * @param int $id
     * @return bool
     */
    public function exists(int $id): bool;

    /**
     * Update an existing item by its ID
     * 
     * @param int $id
     * @param mixed $item
     * @return void
     */
    public function update(int $id, mixed $item): void;

    /**
     * Delete an item by its ID
     * 
     * @param int $id
     * @return void
     */
    public function delete(int $id): void;
}