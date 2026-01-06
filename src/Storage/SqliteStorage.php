<?php

namespace Korriganmaster\LiteCollection\Storage;

use ArrayAccess;
use RuntimeException;
use SQLite3;
use SQLite3Result;
use SQLite3Stmt;

/**
 * Class SqliteStorage
 * @package Korriganmaster\LiteCollection\Storage
 */
class SqliteStorage extends AbstractStorage
{
    /**
     * Path to the SQLite database file
     * defaults to in-memory database
     *
     * @var string $databasePath
     */
    private $databasePath = ':memory:';

    /**
     * SQLite database connection
     *
     * @var SQLite3 $db
     */
    private $db;

    /**
     * Prepared statement for select data
     *
     * @var SQLite3Stmt|false $selectStmt
     */
    private $selectStmt;

    /**
     * Result set from the last query
     *
     * @var SQLite3Result|false $result
     */
    private $result;

    /**
     * Current row data
     *
     * @var array<mixed>|bool $currentRow
     */
    private $currentRow;

    /**
     * Current position in the iterator
     *
     * @var int $position
     */
    private $position = 0;

    /**
     * SqliteStorage constructor.
     *
     * @param int $mode
     * @param string $primaryKey
     * @param string $databasePath
     */
    public function __construct(
        $mode = StorageInterface::MODE_NORMAL,
        $primaryKey = 'id',
        $databasePath = ':memory:'
    ) {
        parent::__construct($mode, $primaryKey);

        $this->databasePath = $databasePath;
        $this->db = new SQLite3($this->databasePath);
        $this->db->query('CREATE TABLE IF NOT EXISTS items (id INTEGER PRIMARY KEY, data BLOB)');
        $this->selectStmt = $this->db->prepare('SELECT * FROM items');

        if ($this->selectStmt === false) {
            throw new RuntimeException('Failed to prepare select statement.');
        }
    }

    /**
     * Destructor to close the database connection
     *
     * @return void
     */
    public function __destruct()
    {
        $this->db->close();

        if ($this->databasePath !== ':memory:') {
            unlink($this->databasePath);
        }
    }

    /**
     * Insert an item into storage
     *
     * @param mixed $item
     * @return void
     */
    public function insert($item)
    {
        $queryString = 'INSERT INTO items (data) VALUES (:data)';
        if ($this->mode === StorageInterface::MODE_ASSOCIATIVE) {
            $queryString = 'INSERT INTO items (id, data) VALUES (:id, :data)';
        }

        $stmt = $this->db->prepare($queryString);

        if ($stmt === false) {
            throw new RuntimeException('Failed to prepare insert statement.');
        }

        $compressedData = gzcompress(serialize($item));
        $stmt->bindValue(':data', $compressedData, SQLITE3_BLOB);

        if ($this->mode === StorageInterface::MODE_ASSOCIATIVE) {
            $id = is_array($item) || $item instanceof ArrayAccess
                ? $item[$this->primaryKey] ?? null
                : $item->{$this->primaryKey} ?? null;
            $stmt->bindValue(':id', $id, SQLITE3_INTEGER);
        }

        $stmt->execute();
    }

    /**
     * Find an item by its ID
     *
     * @param int $id
     * @return mixed
     */
    public function findById($id)
    {
        $stmt = $this->db->prepare('SELECT * FROM items WHERE id = :id');

        if ($stmt === false) {
            throw new RuntimeException('Failed to prepare findById statement.');
        }

        $stmt->bindValue(
            ':id',
            $this->normalizeId($id),
            SQLITE3_INTEGER
        );
        $result = $stmt->execute();

        if ($result === false) {
            return null;
        }

        $row = $result->fetchArray(SQLITE3_ASSOC);
        if ($row) {
            if (!is_string($row['data'])) {
                return null;
            }

            return unserialize((string) gzuncompress((string) $row['data']));
        }
        return null;
    }

    /**
     * Check if an item exists by its ID
     *
     * @param int $id
     * @return bool
     */
    public function exists($id)
    {
        $stmt = $this->db->prepare('SELECT COUNT(*) as count FROM items WHERE id = :id');

        if ($stmt === false) {
            throw new RuntimeException('Failed to prepare exists statement.');
        }

        $stmt->bindValue(
            ':id',
            $this->normalizeId($id),
            SQLITE3_INTEGER
        );
        $result = $stmt->execute();

        if ($result === false) {
            return false;
        }

        $row = $result->fetchArray(SQLITE3_ASSOC);
        return $row
            ? $row['count'] > 0
            : false;
    }

    /**
     * Update an existing item by its ID
     *
     * @param int $id
     * @param mixed $item
     * @return void
     */
    public function update($id, $item)
    {
        $stmt = $this->db->prepare('REPLACE INTO items (id, data) VALUES (:id, :data)');

        if ($stmt === false) {
            throw new RuntimeException('Failed to prepare update statement.');
        }

        $compressedData = gzcompress(serialize($item));
        $stmt->bindValue(':data', $compressedData, SQLITE3_BLOB);
        $stmt->bindValue(
            ':id',
            $this->normalizeId($id),
            SQLITE3_INTEGER
        );
        $stmt->execute();
    }

    /**
     * Delete an item by its ID
     *
     * @param int $id
     * @return void
     */
    public function delete($id)
    {
        $stmt = $this->db->prepare('DELETE FROM items WHERE id = :id');

        if ($stmt === false) {
            throw new RuntimeException('Failed to prepare delete statement.');
        }

        $stmt->bindValue(
            ':id',
            $this->normalizeId($id),
            SQLITE3_INTEGER
        );
        $stmt->execute();

        // If in normal mode, we need to reindex IDs
        if ($this->mode === StorageInterface::MODE_NORMAL) {
            // Rebuild IDs to maintain sequential order
            $stmt = $this->db->prepare('UPDATE items SET id = id - 1 WHERE id > :id');

            if ($stmt === false) {
                throw new RuntimeException('Failed to prepare reindex statement.');
            }

            $stmt->bindValue(':id', $id + 1, SQLITE3_INTEGER);
            $stmt->execute();
        }
    }

    /**
     * Count the number of items in storage
     *
     * @return int
     */
    public function count()
    {
        $countResult = $this->db->querySingle('SELECT COUNT(*) as count FROM items');
        return (int) $countResult;
    }

    /**
     * Get the current item data
     *
     * @return mixed
     */
    public function current()
    {
        if (!is_array($this->currentRow)) {
            return null;
        }

        if (!is_string($this->currentRow['data'])) {
            return null;
        }

        return unserialize((string) gzuncompress((string) $this->currentRow['data']));
    }

    /**
     * Move to the next item in storage
     *
     * @return void
     */
    public function next()
    {
        $this->position++;

        if ($this->result === false) {
            $this->currentRow = false;
            return;
        }

        $this->currentRow = $this->result->fetchArray(SQLITE3_ASSOC);
    }

    /**
     * Get the key of the current item
     *
     * @return mixed
     */
    public function key()
    {
        if ($this->mode === StorageInterface::MODE_ASSOCIATIVE) {
            return is_array($this->currentRow)
                ? $this->currentRow['id']
                : $this->position;
        }

        return $this->position;
    }

    /**
     * Check if the current position is valid
     *
     * @return bool
     */
    public function valid()
    {
        return !empty($this->currentRow);
    }

    /**
     * Rewind to the first item in storage
     *
     * @return void
     */
    public function rewind()
    {
        $this->position = 0;

        if ($this->selectStmt === false) {
            $this->currentRow = false;
            return;
        }

        $this->result = $this->selectStmt->execute();
        $this->currentRow = $this->result === false
            ? false
            : $this->result->fetchArray(SQLITE3_ASSOC);
    }

    /**
     * Normalize ID based on storage mode
     *
     * @param int $id
     * @return int
     */
    private function normalizeId($id)
    {
        return $this->mode === StorageInterface::MODE_ASSOCIATIVE
            ? $id
            : $id + 1;
    }
}
