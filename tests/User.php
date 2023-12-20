<?php

namespace Tests;

use ORM\Mapping as ORM;

#[ORM\Table(tableName: 'users')]
class User
{
    #[ORM\Column('id')]
    private ?int $id;

    #[ORM\Column('title')]
    private ?string $title;

    #[ORM\HasMany(Post::class, targetProperty: 'user')]
    private array $posts = [];

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(?int $id): User
    {
        $this->id = $id;
        return $this;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(?string $title): User
    {
        $this->title = $title;
        return $this;
    }

    public function getPosts(): array
    {
        return $this->posts;
    }

    public function setPosts(array $posts): User
    {
        $this->posts = $posts;
        return $this;
    }
}