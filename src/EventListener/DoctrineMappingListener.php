<?php

namespace Adeliom\EasyPageBundle\EventListener;

use Doctrine\Bundle\DoctrineBundle\Attribute\AsDoctrineListener;
use Doctrine\ORM\Event\LoadClassMetadataEventArgs;
use Doctrine\ORM\Events;
use Doctrine\ORM\Mapping\ClassMetadata;

/**
 * This class adds automatically the ManyToOne and OneToMany relations in Page and Category entities,
 * because it's normally impossible to do so in a mapped superclass.
 */
#[AsDoctrineListener(Events::loadClassMetadata)]
class DoctrineMappingListener
{
    public function __construct(
        /**
         * @readonly
         */
        private string $pageClass
    ) {
    }

    public function loadClassMetadata(LoadClassMetadataEventArgs $eventArgs): void
    {
        $classMetadata = $eventArgs->getClassMetadata();

        $isPage = is_a($classMetadata->getName(), $this->pageClass, true);

        if ($isPage) {
            $this->processParent($classMetadata, $this->pageClass);
            $this->processChildren($classMetadata, $this->pageClass);
        }
    }

    /**
     * Declare self-bidirectionnal mapping for parent.
     */
    private function processParent(ClassMetadata $classMetadata, string $class): void
    {
        if (!$classMetadata->hasAssociation('parent')) {
            $classMetadata->mapManyToOne([
                'fieldName' => 'parent',
                'targetEntity' => $class,
                'inversedBy' => 'children',
                'cascade' => ['persist', 'detach'],
                'joinColumns' => [
                    [
                        'name' => 'parent_id',
                        'referencedColumnName' => 'id',
                        'onDelete' => 'SET NULL',
                    ],
                ],
                'nullable' => true
            ]);
        }
    }

    /**
     * Declare self-bidirectionnal mapping for children.
     */
    private function processChildren(ClassMetadata $classMetadata, string $class): void
    {
        if (!$classMetadata->hasAssociation('children')) {
            $classMetadata->mapOneToMany([
                'fieldName' => 'children',
                'targetEntity' => $class,
                'mappedBy' => 'parent',
            ]);
        }
    }
}
