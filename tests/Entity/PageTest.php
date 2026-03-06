<?php

declare(strict_types=1);

namespace Adeliom\EasyPageBundle\Tests\Entity;

use Adeliom\EasyCommonBundle\Enum\ThreeStateStatusEnum;
use Adeliom\EasyPageBundle\Entity\Page;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Event\PrePersistEventArgs;
use Doctrine\ORM\Event\PreRemoveEventArgs;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(\Adeliom\EasyPageBundle\Entity\Page::class)]
final class PageTest extends TestCase
{
    public function testPageMaintainsHierarchyAndPresentationProperties(): void
    {
        $parent = new Page();
        $parent->setName('Parent');
        $parent->setSlug('parent');

        $child = new Page();
        $child->setName('Child');
        $child->setSlug('child');
        $child->setAction('App\\Controller\\LandingController::index');
        $child->setTemplate('landing');
        $child->setCss('body { color: red; }');
        $child->setJs('console.log("test");');
        $child->setParent($parent);

        self::assertSame($parent, $child->getParent());
        self::assertCount(1, $parent->getChildren());
        self::assertSame('parent/child', $child->getTree());
        self::assertSame('Parent > Child', $child->getTree(' > ', true));
        self::assertSame(' Parent', $parent->getTreeDisplay());
        self::assertSame('― Child', $child->getTreeDisplay());
        self::assertSame('App\\Controller\\LandingController::index', $child->getAction());
        self::assertSame('landing', $child->getTemplate());
        self::assertSame('body { color: red; }', $child->getCss());
        self::assertSame('console.log("test");', $child->getJs());
        self::assertFalse($child->isHomepage());

        $parent->removeChildren($child);

        self::assertCount(0, $parent->getChildren());
    }

    public function testPageLifecycleUpdatesSeoAndRemovalState(): void
    {
        $page = new Page();
        $page->setName('Landing');
        $page->setSlug('landing');
        $page->setTemplate(Page::HOMEPAGE);
        $page->getSEO()->title = '';

        $entityManager = $this->createMock(EntityManagerInterface::class);
        $page->setSeoTitle(new PrePersistEventArgs($page, $entityManager));

        self::assertSame('Landing', $page->getSEO()->title);
        self::assertTrue($page->isHomepage());

        $child = new Page();
        $child->setName('Child');
        $page->addChildren($child);

        $id = new \ReflectionProperty($page, 'id');
        $id->setValue($page, 42);

        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager->expects(self::once())->method('persist')->with($child);
        $preRemove = new PreRemoveEventArgs($page, $entityManager);

        $page->onRemove($preRemove);

        self::assertSame(ThreeStateStatusEnum::UNPUBLISHED, $page->getState());
        self::assertNull($page->getParent());
        self::assertSame('Landing-42-deleted', $page->getName());
        self::assertSame('landing-42-deleted', $page->getSlug());
        self::assertNull($child->getParent());
    }
}
