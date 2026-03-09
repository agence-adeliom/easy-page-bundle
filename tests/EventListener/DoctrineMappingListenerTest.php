<?php

declare(strict_types=1);

namespace Adeliom\EasyPageBundle\Tests\EventListener;

use Adeliom\EasyPageBundle\EventListener\DoctrineMappingListener;
use Adeliom\EasyPageBundle\Tests\Fixtures\Entity\TestPage;
use Doctrine\ORM\Event\LoadClassMetadataEventArgs;
use Doctrine\ORM\Mapping\ClassMetadata;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(\Adeliom\EasyPageBundle\EventListener\DoctrineMappingListener::class)]
final class DoctrineMappingListenerTest extends TestCase
{
    public function testListenerMapsParentAndChildrenAssociations(): void
    {
        $metadata = $this->getMockBuilder(ClassMetadata::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getName', 'hasAssociation', 'mapManyToOne', 'mapOneToMany'])
            ->getMock();

        $metadata->method('getName')->willReturn(TestPage::class);
        $metadata->method('hasAssociation')->willReturn(false);
        $metadata->expects(self::once())->method('mapManyToOne')
            ->with(self::callback(fn ($m) => $m['fieldName'] === 'parent' && $m['targetEntity'] === TestPage::class));
        $metadata->expects(self::once())->method('mapOneToMany')
            ->with(self::callback(fn ($m) => $m['fieldName'] === 'children' && $m['targetEntity'] === TestPage::class));

        $event = $this->createMock(LoadClassMetadataEventArgs::class);
        $event->method('getClassMetadata')->willReturn($metadata);

        (new DoctrineMappingListener(TestPage::class))->loadClassMetadata($event);
    }
}
