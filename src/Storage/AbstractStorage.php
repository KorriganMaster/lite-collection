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
    protected int $mode = StorageInterface::MODE_NORMAL;

    /**
     * Private key field name
     *
     * @var string $privateKey
     */
    protected string $primaryKey = 'id';

    /**
     * AbstractStorage constructor.
     *
     * @param int $mode
     */
    public function __construct(
        int $mode = StorageInterface::MODE_NORMAL,
        string $primaryKey = 'id',
    ) {
        $this->mode = $mode;
        $this->primaryKey = $primaryKey;
    }
}
