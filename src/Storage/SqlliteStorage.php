<?php

namespace Korriganmaster\LiteCollection\Storage;

use SQLite3;
use SQLite3Result;
use SQLite3Stmt;

/**
 * Class SqlliteStorage
 * @package Korriganmaster\LiteCollection\Storage
 */
class SqlliteStorage extends AbstractStorage
{
    /**
     * Path to the SQLite database file
     * defaults to in-memory database
     * 
     * @var string $databasePath
     */
    private string $databasePath = ':memory:';

    /**
     * SQLite database connection
     * 
     * @var SQLite3 $db
     */
    private SQLite3 $db;

    /**
     * Prepared statement for select data
     * 
     * @var SQLite3Stmt $selectStmt
     */
    private SQLite3Stmt $selectStmt;

    /**
     * Result set from the last query
     * 
     * @var SQLite3Result $result
     */
    private SQLite3Result $result;

    /**
     * Current row data
     * 
     * @var array|bool $currentRow
     */
    private mixed $currentRow;

    /**
     * Current position in the iterator
     * 
     * @var int $position
     */
    private int $position = 0;

    /**
     * SqlliteStorage constructor.
     * 
     * @param string $databasePath
     */
    public function __construct(
        string $databasePath = ':memory:', 
        int $mode = StorageInterface::MODE_NORMAL,
        string $primaryKey = 'id',
    ) {
        parent::__construct($mode, $primaryKey);

        $this->databasePath = $databasePath;
        $this->db = new SQLite3($this->databasePath);
        $this->db->query('CREATE TABLE IF NOT EXISTS items (id INTEGER PRIMARY KEY, data BLOB)');
        $this->selectStmt = $this->db->prepare('SELECT * FROM items');
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
    public function insert(mixed $item): void
    {   $queryString = 'INSERT INTO items (data) VALUES (:data)';
        if ($this->mode === StorageInterface::MODE_ASSOCIATIVE) {
            $queryString = 'INSERT INTO items (id, data) VALUES (:id, :data)';
        }

        $stmt = $this->db->prepare($queryString);
        $compressedData = gzcompress(serialize($item));
        $stmt->bindValue(':data', $compressedData, SQLITE3_BLOB);

        if ($this->mode === StorageInterface::MODE_ASSOCIATIVE) {
            $id = $item[$this->primaryKey] ?? null;
            $stmt->bindValue(':id', $id, SQLITE3_INTEGER);
        }

        $stmt->execute();
    }

    /**
     * Find an item by its ID
     * 
     * @param int $id
     * @return array|null
     */
    public function findById(int $id): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM items WHERE id = :id');
        $stmt->bindValue(':id', $id, SQLITE3_INTEGER);
        $result = $stmt->execute();
        $row = $result->fetchArray(SQLITE3_ASSOC);
        if ($row) {
            return unserialize(gzuncompress($row['data']));
        }
        return null;
    }

    /**
     * Count the number of items in storage
     * 
     * @return int
     */
    public function count(): int
    {
        $countResult = $this->db->querySingle('SELECT COUNT(*) as count FROM items');
        return (int)$countResult;
    }

    /**
     * Get the current item data
     * 
     * @return mixed
     */
    public function current(): mixed
    {
        return unserialize(gzuncompress($this->currentRow['data']));
    }

    /**
     * Move to the next item in storage
     * 
     * @return void
     */
    public function next(): void
    {
        $this->position++;
        $this->currentRow = $this->result->fetchArray(SQLITE3_ASSOC);
    }

    /**
     * Get the key of the current item
     * 
     * @return mixed
     */
    public function key(): mixed
    {
        if ($this->mode === StorageInterface::MODE_ASSOCIATIVE) {
            return $this->currentRow['id'];
        }

        return $this->position;
    }

    /**
     * Check if the current position is valid
     * 
     * @return bool
     */
    public function valid(): bool
    {
        return $this->currentRow !== false && $this->currentRow !== null;
    }

    /**
     * Rewind to the first item in storage
     * 
     * @return void
     */
    public function rewind(): void
    {
        $this->position = 0;
        $this->result = $this->selectStmt->execute();
        $this->currentRow = $this->result->fetchArray(SQLITE3_ASSOC);
    }
}