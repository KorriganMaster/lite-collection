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
    public const MODE_NORMAL = 1;
    public const MODE_ASSOCIATIVE = 2;

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
    public function insert($item);

    /**
     * Find an item by its ID
     *
     * @param int $id
     * @return mixed
     */
    public function findById($id);

    /**
     * Check if an item exists by its ID
     *
     * @param int $id
     * @return bool
     */
    public function exists($id);

    /**
     * Update an existing item by its ID
     *
     * @param int $id
     * @param mixed $item
     * @return void
     */
    public function update($id, $item);

    /**
     * Delete an item by its ID
     *
     * @param int $id
     * @return void
     */
    public function delete($id);
}
