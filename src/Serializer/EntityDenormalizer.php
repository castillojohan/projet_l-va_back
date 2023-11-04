<?php

namespace App\Serializer;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

/**
 * Entity normalizer by benlac
 */
class EntityDenormalizer implements DenormalizerInterface
{
    /** @var EntityManagerInterface **/
    protected $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

      /**
     * Determines if this denormalizer supports the given data and type.
     *
     * In this example:
     * - $data: 1 (an identifier)
     * - $type: Entity \App\Entity\Genre (entity class name)
     *
     * @inheritDoc
     *
     * @param mixed $data The data to be denormalized (e.g., an ID).
     * @param string $type The type to denormalize to (e.g., entity class name).
     * @param string|null $format The format being deserialized (default is null).
     *
     * @return bool Returns true if the denormalization is supported; otherwise, false.
     */
    public function supportsDenormalization($data, $type, $format = null)
    {
        return strpos($type, 'App\\Entity\\') === 0 && (is_numeric($data));
    }

     /**
     * Denormalizes the data into a Doctrine entity object.
     *
     * @inheritDoc
     *
     * @param mixed $data The data to be denormalized (e.g., an ID).
     * @param string $class The class to denormalize to (e.g., entity class name).
     * @param string|null $format The format being deserialized (default is null).
     * @param array $context The context options for denormalization (default is an empty array).
     *
     * @return object|null Returns the denormalized entity object or null if not found.
     */
    public function denormalize($data, $class, $format = null, array $context = [])
    {
        return $this->entityManager->find($class, $data);
    }
}
