<?php

namespace Wan\ContingencyBundle\Entity\SsApi\Traits;

/**
 * This trait is useful for entities with a StartDate and EndDate to indicate if it is active, and where there are
 * historical records
 *
 * @author matthewfl
 */
trait JsonDeserialize
{
    protected static $className;

    protected function deserializeEntities($property, $jsonValue)
    {
        $upper = ucfirst($property);
        $lower = lcfirst($property);
        $dePluralized = substr($upper, 0, -1);
        $dpLower = lcfirst($dePluralized);

        foreach ([static::$belongsTo, static::$hasOne] as $config) {
            foreach ([$upper, $lower, $dePluralized, $dpLower] as $case) {
                if (isset($config[$case])) {
                    $entityName = isset($config[$case]['table']) ? $config[$case]['table'] : ucfirst($case);
                    $repo = $this->client->getRepository($entityName);
                    return $repo->deserializeJson($jsonValue);
                }
            }
        }

        foreach ([$upper, $lower, $dePluralized, $dpLower] as $case) {
            if (isset(static::$hasMany[$case])) {
                $entityName = isset(static::$hasMany[$case]['table']) ? static::$hasMany[$case]['table'] : ucfirst($case);
                $repo = $this->client->getRepository($entityName);
                $value = [];
                foreach ($jsonValue as $jsonElement) {
                    $value[] = $repo->deserializeJson($jsonElement);
                }
                return $value;
            }
        }

        return $jsonValue;
    }

    public function deserializeJson($json)
    {
        $className = __NAMESPACE__ . '\\' . static::ENTITY_NAME;

        $entity = new $className();

        foreach ($json as $property => $jsonValue) {
            if (!$jsonValue) {
                continue;
            }
            $setter = 'set' . ucfirst($property);
            if (!method_exists($entity, $setter)) {
                $this->logger->warning("API returned property $property for entity $className but a setter does not exist - skipping");
                continue;
            }

            $value = $this->deserializeEntities($property, $jsonValue);

            $entity->$setter($value);
        }
        return $entity;
    }

}
