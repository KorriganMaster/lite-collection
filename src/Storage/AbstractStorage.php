<?php

namespace Korriganmaster\LiteCollection\Storage;

/**
 * Trait StorageTrait
 * @package Korriganmaster\LiteCollection\Storage
 */
abstract class AbstractStorage implements StorageInterface
{
    /**
     * Storage mode
     *
     * @var int $mode
     */
    protected $mode = StorageInterface::MODE_NORMAL;

    /**
     * Private key field name
     *
     * @var string $primaryKey
     */
    protected $primaryKey = 'id';

    /**
     * AbstractStorage constructor.
     *
     * @param int $mode
     * @param string $primaryKey
     */
    public function __construct(
        $mode = StorageInterface::MODE_NORMAL,
        $primaryKey = 'id'
    ) {
        $this->mode = $mode;
        $this->primaryKey = $primaryKey;
    }
}
