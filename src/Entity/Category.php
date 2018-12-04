<?php

namespace App\Entity;

use Doctrine\Common\Annotations\Annotation\Target;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\CategoryRepository")
 *
 * Validation : contrainte d'unicité sur le nom
 * @UniqueEntity(fields={"name"}, message="Cette catégorie existe déjà.")
 */
class Category
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=20, unique=true)
     *
     * Validation : non vide
     * @Assert\NotBlank(message="Le nom est obligatoire.")
     * Validation : nombre max de caractère 20 (min existe)
     * @Assert\Length(max="20", maxMessage="Le nom ne doit pas dépasser {{ limit }} caractères.")
     */
    private $name;

    /**
     * @ORM\OneToMany(targetEntity="Article", mappedBy="category")
     * @ORM\JoinColumn()
     */
    private $article;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function __toString()
    {
        return $this->getName();
    }

    /**
     * @return Article
     */
    public function getArticle(): Collection
    {
        return $this->article;
    }

    /**
     * @param Article $article
     * @return Category
     */
    public function setArticle(Article $article): Category
    {
        $this->article = $article;
        return $this;
    }


}
