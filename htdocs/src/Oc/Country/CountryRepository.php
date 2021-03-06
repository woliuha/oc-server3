<?php

namespace Oc\Country;

use Doctrine\DBAL\Connection;
use Oc\Repository\Exception\RecordAlreadyExistsException;
use Oc\Repository\Exception\RecordNotPersistedException;
use Oc\Repository\Exception\RecordsNotFoundException;

/**
 * Class CountryRepository
 *
 * @package Oc\Country
 */
class CountryRepository
{
    /**
     * Database table name that this repository maintains.
     *
     * @var string
     */
    const TABLE = 'countries';

    /**
     * @var Connection
     */
    private $connection;

    /**
     * CountryRepository constructor.
     *
     * @param Connection $connection
     */
    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    /**
     * Fetches all countries.
     *
     * @return CountryEntity[]
     *
     * @throws RecordsNotFoundException Thrown when no records are found
     */
    public function fetchAll()
    {
        $statement = $this->connection->createQueryBuilder()
            ->select('*')
            ->from(self::TABLE)
            ->execute();

        $result = $statement->fetchAll();

        if ($statement->rowCount() === 0) {
            throw new RecordsNotFoundException('No records found');
        }

        $countries = [];

        foreach ($result as $item) {
            $countries[] = $this->getEntityFromDatabaseArray($item);
        }

        return $countries;
    }

    /**
     * Creates a country in the database.
     *
     * @param CountryEntity $entity
     *
     * @return CountryEntity
     *
     * @throws RecordAlreadyExistsException
     */
    public function create(CountryEntity $entity)
    {
        if (!$entity->isNew()) {
            throw new RecordAlreadyExistsException('The entity does already exist.');
        }

        $databaseArray = $this->getDatabaseArrayFromEntity($entity);

        $this->connection->insert(
            self::TABLE,
            $databaseArray
        );

        $entity->short = $this->connection->lastInsertId();
    }

    /**
     * Update a country in the database.
     *
     * @param CountryEntity $entity
     *
     * @return CountryEntity
     *
     * @throws RecordNotPersistedException
     */
    public function update(CountryEntity $entity)
    {
        if ($entity->isNew()) {
            throw new RecordNotPersistedException('The entity does not exist.');
        }

        $databaseArray = $this->getDatabaseArrayFromEntity($entity);

        $this->connection->update(
            self::TABLE,
            $databaseArray,
            ['short' => $entity->short]
        );

        $entity->short = $this->connection->lastInsertId();

        return $entity;
    }

    /**
     * Removes a country from the database.
     *
     * @param CountryEntity $entity
     *
     * @return CountryEntity
     *
     * @throws RecordNotPersistedException
     */
    public function remove(CountryEntity $entity)
    {
        if ($entity->isNew()) {
            throw new RecordNotPersistedException('The entity does not exist.');
        }

        $databaseArray = $this->getDatabaseArrayFromEntity($entity);

        $this->connection->delete(
            self::TABLE,
            $databaseArray,
            ['short' => $entity->short]
        );

        $entity->short = null;

        return $entity;
    }

    /**
     * Maps the given entity to the database array.
     *
     * @param CountryEntity $entity
     *
     * @return array
     */
    public function getDatabaseArrayFromEntity(CountryEntity $entity)
    {
        return [
            'short' => $entity->short,
            'name' => $entity->name,
            'de' => $entity->de,
            'en' => $entity->en,
            'trans_id' => $entity->translationId,
            'list_default_de' => $entity->listDefaultDe,
            'list_default_en' => $entity->listDefaultEn,
            'sort_de' => $entity->sortDe,
            'sort_en' => $entity->sortEn
        ];
    }

    /**
     * Prepares database array from properties.
     *
     * @param array $data
     *
     * @return CountryEntity
     */
    public function getEntityFromDatabaseArray(array $data)
    {
        $entity = new CountryEntity();
        $entity->short = (string) $data['short'];
        $entity->name = (string) $data['name'];
        $entity->de = (string) $data['de'];
        $entity->en = (string) $data['en'];
        $entity->translationId = (int) $data['trans_id'];
        $entity->listDefaultDe = (bool) $data['list_default_de'];
        $entity->listDefaultEn = (bool) $data['list_default_en'];
        $entity->sortDe = (string) $data['sort_de'];
        $entity->sortEn = (string) $data['sort_en'];

        return $entity;
    }
}
