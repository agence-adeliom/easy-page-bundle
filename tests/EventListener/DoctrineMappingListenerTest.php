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
        $metadata = new ClassMetadata(TestPage::class);
        $event = $this->createMock(LoadClassMetadataEventArgs::class);
        $event->method('getClassMetadata')->willReturn($metadata);

        (new DoctrineMappingListener(TestPage::class))->loadClassMetadata($event);

        self::assertTrue($metadata->hasAssociation('parent'));
        self::assertTrue($metadata->hasAssociation('children'));
    }
}
