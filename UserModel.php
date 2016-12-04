<?php

namespace Models;

use MongoDB\MongoDBModel;

/**
 * User model
 *
 * @package Models
 * @author leandro <leandro_mnc@yahoo.com.br>
 */
class UserModel extends MongoDBModel
{

    /**
     * Database name
     *
     * @var string
     */
    protected $database = 'test';

    /**
     * Collection name
     *
     * @var string
     */
    private $collection = 'user';

    /**
     * Insert new user
     *
     * @return void
     */
    public function insertUser()
    {
        $data['name'] = 'John';
        $data['age'] = 30;
        $data['phones'] = [
            [
                //'_id' => $this->getObjectId(),
                'area' => 11,
                'number' => 983055557,
                'type' => 'mobile'
            ],
            [
                //'_id' => $this->getObjectId(),
                'area' => 11,
                'number' => 50722540,
                'type' => 'home'
            ],
        ];

        $this->insert($this->collection, $data);
    }

    /**
     * Update user
     *
     * @return void
     */
    public function updateUser()
    {
        $data = $this->findOne($this->collection, ['idade' => 30]);

        $data->phones[1] = [
            'area' => 11,
            'number' => 12345678,
            'type' => 'home'
        ];

        $filter = ['_id' => $data->_id];
        $this->update($this->collection, $filter, $data);
    }

    /**
     * Get all users
     *
     * @return type
     */
    public function getAllUsers()
    {
        return $this->find($this->collection);
    }

    /**
     * Remove all users
     *
     * @return void
     */
    public function removeAll()
    {
        $lista = $this->getAllUsers();

        foreach ($lista as $doc) {
            $data['_id'] = $doc->_id;
            $this->delete($this->collection, $data);
        }
    }
}
