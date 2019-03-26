<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Bundle\Sql;

use Akeneo\Pim\Structure\Component\AttributeTypes;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Types\Type;

class AttributeRepository
{
    /** @var Connection */
    private $connection;

    /**
     * @param Connection $connection
     */
    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    /**
     * @param string $code
     *
     * @return Attribute|null
     * @throws \Doctrine\DBAL\DBALException
     */
    public function findOneByIdentifier(string $code): ?Attribute
    {
        $rows =  $this->findSeveralByIdentifiers([$code]);

        return $rows[$code];
    }

    /**
     * @param string[] $codes
     *
     * @return Attribute[]
     * @throws \Doctrine\DBAL\DBALException
     */
    public function findSeveralByIdentifiers(array $codes): array
    {
        if (empty($codes)) {
            return [];
        }

        $results = [];
        foreach ($codes as $code) {
            $results[$code] = null;
        }

        $sql = <<<SQL
          SELECT
            a.*,
            attribute_group.code AS group_code,
            JSON_ARRAYAGG(locale.code) AS available_locales
          FROM
            pim_catalog_attribute a
            JOIN pim_catalog_attribute_group attribute_group ON attribute_group.id = a.group_id
            LEFT JOIN pim_catalog_attribute_locale al ON al.attribute_id = a.id
            LEFT JOIN pim_catalog_locale locale on locale.id = al.locale_id
          WHERE 
            a.code IN (:codes)
		  GROUP BY a.id
SQL;

        $rows = $this->connection->executeQuery($sql,
            ['codes' => $codes],
             ['codes' => Connection::PARAM_STR_ARRAY]

        )->fetchAll();

        foreach ($rows as $row) {
            $results[$row['code']] = new Attribute(
                (int) $row['id'],
                $row['code'],
                $row['attribute_type'],
                $row['backend_type'],
                (bool) $row['is_required'],
                (bool) $row['is_unique'],
                (bool) $row['is_localizable'],
                (bool) $row['is_scopable'],
                new ArrayCollection(unserialize($row['properties'])),
                // options
                $row['group_code'],
                (bool) $row['useable_as_grid_filter'],
                array_filter(json_decode($row['available_locales'], true)),
                (int) $row['max_characters'],
                $row['validation_rule'],
                $row['validation_regexp'],
                null === $row['number_min'] ? null : (int)$row['number_min'],
                null === $row['number_max'] ? null : (int)$row['number_max'],
                (bool) $row['decimals_allowed'],
                (bool) $row['negative_allowed'],
                Type::getType(Type::DATETIME)->convertToPHPValue($row['date_min'], $this->connection->getDatabasePlatform()),
                Type::getType(Type::DATETIME)->convertToPHPValue($row['date_max'], $this->connection->getDatabasePlatform()),
                $row['metric_family'],
                $row['default_metric_unit'],
                (int) $row['max_file_size'],
                null === $row['allowed_extensions'] || '' === $row['allowed_extensions'] ? [] : explode(',', $row['allowed_extensions']),
                (int) $row['minimumInputLength']
            );
        }

        return $results;
    }

    /**
     * @return string
     * @throws \Doctrine\DBAL\DBALException
     */
    public function getIdentifierCode(): string
    {
        $sql = <<<SQL
SELECT a.code
FROM pim_catalog_attribute a
WHERE a.attribute_type = :type;
SQL;
        $rows = $this->connection->executeQuery(
            $sql,
            ['type' => AttributeTypes::IDENTIFIER]
        )->fetchAll();

        return $rows[0]['code'];
    }

    /**
     * FIXME: ugly, but the quickest way to go on with the POC, as it is needed by
     * Akeneo\Pim\Enrichment\Component\Product\Connector\ArrayConverter\FlatToStandard\AttributeColumnsResolver::resolveAttributeColumns()
     *
     * @return Attribute[]
     */
    public function findAll(): array
    {
        $sql = <<<SQL
          SELECT
            a.*,
            attribute_group.code AS group_code,
            JSON_ARRAYAGG(locale.code) AS available_locales
          FROM
            pim_catalog_attribute a
            JOIN pim_catalog_attribute_group attribute_group ON attribute_group.id = a.group_id
            LEFT JOIN pim_catalog_attribute_locale al ON al.attribute_id = a.id
            LEFT JOIN pim_catalog_locale locale on locale.id = al.locale_id          
		  GROUP BY a.id
SQL;

        $results = [];
        $rows = $this->connection->executeQuery($sql)->fetchAll();

        foreach ($rows as $row) {
            $results[$row['code']] = new Attribute(
                (int)$row['id'],
                $row['code'],
                $row['attribute_type'],
                $row['backend_type'],
                (bool)$row['is_required'],
                (bool)$row['is_unique'],
                (bool)$row['is_localizable'],
                (bool)$row['is_scopable'],
                new ArrayCollection(unserialize($row['properties'])),
                // options
                $row['group_code'],
                (bool)$row['useable_as_grid_filter'],
                array_filter(json_decode($row['available_locales'], true)),
                (int)$row['max_characters'],
                $row['validation_rule'],
                $row['validation_regexp'],
                null === $row['number_min'] ? null : (int)$row['number_min'],
                null === $row['number_max'] ? null : (int)$row['number_max'],
                (bool)$row['decimals_allowed'],
                (bool)$row['negative_allowed'],
                Type::getType(Type::DATETIME)->convertToPHPValue(
                    $row['date_min'],
                    $this->connection->getDatabasePlatform()
                ),
                Type::getType(Type::DATETIME)->convertToPHPValue(
                    $row['date_max'],
                    $this->connection->getDatabasePlatform()
                ),
                $row['metric_family'],
                $row['default_metric_unit'],
                (int)$row['max_file_size'],
                null === $row['allowed_extensions'] || '' === $row['allowed_extensions'] ? [] : explode(',', $row['allowed_extensions']),
                (int)$row['minimumInputLength']
            );
        }

        return $results;
    }
}