<?php 

namespace Korriganmaster\LiteCollection\Storage;

use Countable;
use Iterator;

/**
 * Interface StorageInterface
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
     * @return array|null
     */
    public function findById(int $id): ?array;
}