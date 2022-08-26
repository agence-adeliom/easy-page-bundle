<?php

namespace Adeliom\EasyPageBundle\Entity;

use Adeliom\EasyCommonBundle\Enum\ThreeStateStatusEnum;
use Adeliom\EasyCommonBundle\Traits\EntityIdTrait;
use Adeliom\EasyCommonBundle\Traits\EntityNameSlugTrait;
use Adeliom\EasyCommonBundle\Traits\EntityPublishableTrait;
use Adeliom\EasyCommonBundle\Traits\EntityThreeStateStatusTrait;
use Adeliom\EasyCommonBundle\Traits\EntityTimestampableTrait;
use Adeliom\EasySeoBundle\Traits\EntitySeoTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[UniqueEntity('slug')]
#[ORM\HasLifecycleCallbacks]
#[ORM\MappedSuperclass(repositoryClass: \Adeliom\EasyPageBundle\Repository\PageRepository::class)]
class Page
{
    use EntityIdTrait;
    use EntityTimestampableTrait {
        EntityTimestampableTrait::__construct as private TimestampableConstruct;
    }
    use EntityNameSlugTrait;
    use EntityThreeStateStatusTrait {
        EntityThreeStateStatusTrait::__construct as private StateStatusConstruct;
    }
    use EntityPublishableTrait {
        EntityPublishableTrait::__construct as private PublishableConstruct;
    }
    use EntitySeoTrait {
        EntitySeoTrait::__construct as private SEOConstruct;
    }
    /**
     * @var string
     */
    public const HOMEPAGE = 'homepage';

    /**
     * @var Page|null
     */
    #[Assert\Type(Page::class)]
    protected $parent;

    /**
     * @var Page[]|ArrayCollection
     */
    protected $children;

    #[ORM\Column(name: 'action', type: \Doctrine\DBAL\Types\Types::STRING, nullable: true)]
    #[Assert\Type('string')]
    protected ?string $action = null;

    #[Groups('main')]
    #[ORM\Column(name: 'template', type: \Doctrine\DBAL\Types\Types::STRING, nullable: true)]
    #[Assert\Type('string')]
    protected ?string $template = null;

    #[ORM\Column(name: 'css', type: \Doctrine\DBAL\Types\Types::TEXT, nullable: true)]
    #[Assert\Type('string')]
    protected ?string $css = null;

    #[ORM\Column(name: 'js', type: \Doctrine\DBAL\Types\Types::TEXT, nullable: true)]
    #[Assert\Type('string')]
    protected ?string $js = null;

    public function __construct()
    {
        $this->TimestampableConstruct();
        $this->PublishableConstruct();
        $this->SEOConstruct();
        $this->StateStatusConstruct();
        $this->children = new ArrayCollection();
    }

    public function setParent(?Page $parent = null)
    {
        if ($parent === $this) {
            // Refuse the category to have itself as parent.
            $this->parent = null;

            return;
        }

        $this->parent = $parent;

        // Ensure bidirectional relation is respected.
        if ($parent && false === $parent->getChildren()->indexOf($this)) {
            $parent->addChildren($this);
        }
    }

    public function getParent()
    {
        return $this->parent;
    }

    /**
     * @return Page[]|ArrayCollection
     */
    public function getChildren(): array|ArrayCollection
    {
        return $this->children;
    }

    public function addChildren(Page $page): void
    {
        $this->children->add($page);

        if ($page->getParent() !== $this) {
            $page->setParent($this);
        }
    }

    public function removeChildren(Page $page): void
    {
        $this->children->removeElement($page);
    }

    public function getTree(string $separator = '/', bool $name = false): string
    {
        $tree = '';

        $current = $this;
        do {
            $tree = $name ? $current->getName().$separator.$tree : $current->getSlug().$separator.$tree;
            $current = $current->getParent();
        } while ($current);

        return trim($tree, $separator);
    }

    public function getTreeDisplay(): string
    {
        $tree = ' '.$this->getName();

        $current = $this;
        do {
            $tree = '―'.$tree;
            $current = $current->getParent();
        } while ($current);

        return mb_substr($tree, 1);
    }

    public function isHomepage(): bool
    {
        return self::HOMEPAGE == $this->template;
    }

    public function getAction(): ?string
    {
        return $this->action;
    }

    public function setAction(?string $action): void
    {
        $this->action = $action;
    }

    public function getTemplate(): ?string
    {
        return $this->template;
    }

    public function setTemplate(?string $template): void
    {
        $this->template = $template;
    }

    public function getCss(): ?string
    {
        return $this->css;
    }

    public function setCss(string $css): void
    {
        $this->css = $css;
    }

    public function getJs(): ?string
    {
        return $this->js;
    }

    public function setJs(string $js): void
    {
        $this->js = $js;
    }

    #[ORM\PrePersist]
    #[ORM\PreUpdate]
    public function setSeoTitle(LifecycleEventArgs $event): void
    {
        if (empty($this->getSEO()->title)) {
            $this->getSEO()->title = $this->getName();
        }
    }

    #[ORM\PreRemove]
    public function onRemove(LifecycleEventArgs $event): void
    {
        $em = $event->getEntityManager();
        if (null !== $this->children && count($this->children)) {
            foreach ($this->children as $child) {
                $child->setParent(null);
                $em->persist($child);
            }
        }

        $this->setState(ThreeStateStatusEnum::UNPUBLISHED());
        $this->parent = null;
        $this->setName($this->getName().'-'.$this->getId().'-deleted');
        $this->setSlug($this->getSlug().'-'.$this->getId().'-deleted');
    }
}
