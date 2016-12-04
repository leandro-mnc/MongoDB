<?php

namespace MongoDB;

/**
 * MIT License
 * Copyright (c) [2016] [Leandro Teixeira]
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:

 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.

 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 */

/**
 * Bridge to the MongoDB
 *
 * @package MongoDB
 * @author Leandro Teixeira <leandro_mnc@yahoo.com.br>
 */
class MongoDBModel
{

    /**
     * Mongo Connection
     *
     * @var \MongoDB\Driver\Manager
     */
    private $manager = [];

    /**
     * Database name
     *
     * @var string
     */
    private $database = 'test';

    /**
     * Constructor
     *
     * @return void
     */
    public function __construct()
    {
        $this->manager = MongoDBConnection::getInstance();
    }

    /**
     * Execute query
     *
     * @param string $collection
     * @param array $filter
     * @param array $options
     * @return array
     */
    public function find($collection, $filter = [], $options = [])
    {
        $this->setDatabase();

        $query = new \MongoDB\Driver\Query($filter, $options);
        $dbCollection = $this->getDBCollection($collection);
        $cursor = $this->manager->executeQuery($dbCollection, $query);

        $data = [];

        foreach ($cursor as $document) {
            $data[] = $document;
        }

        return $data;
    }

    /**
     * Find one document
     *
     * @param string $collection
     * @param array $filter
     * @param array $options
     * @return array
     */
    public function findOne($collection, $filter = [], $options = [])
    {
        $data = $this->find($collection, $filter, $options);
        return isset($data[0]) ? $data[0] : null;
    }
    
    /**
     * Delete document
     *
     * @param string $collection
     * @param array $filter
     */
    public function delete($collection, $filter = [])
    {
        $bulk = $this->getBulkWrite();
        $bulk->delete($filter);

        try {
            return $this->executeBulkWrite($collection, $bulk);
        } catch (Exception $ex) {
            throw new Exception($ex->getMessage());
        }
    }

    /**
     * Insert document
     *
     * @param string $collection
     * @param array $data
     * @throws Exception
     */
    public function insert($collection, $data)
    {
        $bulk = $this->getBulkWrite();
        $bulk->insert($data);

        try {
            return $this->executeBulkWrite($collection, $bulk);
        } catch (\MongoException $ex) {
            throw new Exception($ex->getMessage());
        }
    }

    /**
     * Update document
     *
     * @param string $collection
     * @param array $filter
     * @param array $newData
     */
    public function update($collection, $filter, $newData = [])
    {
        $bulk = $this->getBulkWrite();
        $bulk->update(
            $filter,
            ['$set' => $newData],
            ['multi' => false, 'upsert' => false]
        );

        try {
            return $this->executeBulkWrite($collection, $bulk);
        } catch (\MongoException $ex) {
            throw new Exception($ex->getMessage());
        }
    }

    /**
     * Get object id
     *
     * @return \MongoDB\BSON\ObjectID
     */
    public function getObjectId()
    {
        return new \MongoDB\BSON\ObjectID;
    }

    /**
     * Get bulk write
     *
     * @return \MongoDB\Driver\BulkWrite
     */
    private function getBulkWrite()
    {
        return new \MongoDB\Driver\BulkWrite;
    }
    
    /**
     * Execute bulk write
     *
     * @param type $collection
     * @param type $bulk
     * @return type
     */
    private function executeBulkWrite($collection, $bulk)
    {
        $this->setDatabase();

        $dbCollection = $this->getDBCollection($collection);

        return $this->manager->executeBulkWrite($dbCollection, $bulk);
    }

    /**
     * Execute command
     *
     * @param array $data
     * @return type
     */
    private function executeCommand($data = [])
    {
        $this->setDatabase();

        $cmd = new \MongoDB\Driver\Command($data);

        return $this->manager->executeCommand($this->database, $cmd);
    }

    /**
     * Set database name from parent class
     *
     * @return void
     */
    private function setDatabase()
    {
        $parentVars = get_class_vars(get_class($this));

        if (isset($parentVars['database']) && !empty($parentVars['database'])) {
            $this->database = $parentVars['database'];
        }
    }

    /**
     * Join database name and collection name
     *
     * @param string $collection
     * @return string
     */
    private function getDBCollection($collection)
    {
        return $this->database . '.' . $collection;
    }
}

/**
 * Connection to MongoDB
 *
 * @package MongoDB
 * @author Leandro Teixeira <leandro_mnc@yahoo.com.br>
 */
class MongoDBConnection
{

    /**
     * MongoDBConnection
     *
     * @var MongoDBConnection
     */
    private static $instance;

    /**
     * MongoDB Manager
     *
     * @var \MongoDB\Driver\Manager
     */
    private $manager;

    /**
     * Constructor
     *
     * @return void
     */
    private function __construct()
    {
        $server = 'mongodb://localhost:27017';
        $this->manager = new \MongoDB\Driver\Manager($server);
    }

    /**
     * Mongo Connection
     *
     * @param string $database
     * @return \MongoDB\Driver\Manager
     */
    public static function getInstance()
    {
        if (null === self::$instance) {
            self::$instance = new MongoDBConnection();
        }
        return self::$instance->manager;
    }
}
