<?php

declare(strict_types=1);

namespace Adeliom\EasyPageBundle\Tests\EventListener;

use Adeliom\EasyPageBundle\EventListener\DoctrineMappingListener;
use Adeliom\EasyPageBundle\Tests\Fixtures\Entity\TestPage;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Event\LoadClassMetadataEventArgs;
use Doctrine\ORM\Mapping\ClassMetadata;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(\Adeliom\EasyPageBundle\EventListener\DoctrineMappingListener::class)]
final class DoctrineMappingListenerTest extends TestCase
{
    public function testListenerMapsParentAndChildrenAssociations(): void
    {
        $metadata = new ClassMetadata(TestPage::class);
        $event = new LoadClassMetadataEventArgs($metadata, $this->createMock(EntityManagerInterface::class));
        $listener = new DoctrineMappingListener(TestPage::class);

        $listener->loadClassMetadata($event);
        $listener->loadClassMetadata($event);

        $parentMapping = $metadata->getAssociationMapping('parent');
        $childrenMapping = $metadata->getAssociationMapping('children');

        self::assertCount(2, $metadata->getAssociationMappings());
        self::assertSame(TestPage::class, $parentMapping->targetEntity);
        self::assertSame('children', $parentMapping->inversedBy);
        self::assertTrue($parentMapping->joinColumns[0]->nullable);
        self::assertSame('SET NULL', $parentMapping->joinColumns[0]->onDelete);
        self::assertSame(TestPage::class, $childrenMapping->targetEntity);
        self::assertSame('parent', $childrenMapping->mappedBy);
    }
}
