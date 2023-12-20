<?php

namespace Tests;

use ORM\Mapping as ORM;

#[ORM\Table('posts')]
class Post
{
    #[ORM\Column('id')]
    private ?int $id;

    #[ORM\Column('content')]
    private ?string $content;

    #[ORM\Column('user_id')]
    #[ORM\BelongsTo(inversedBy: 'posts', entity: User::class)]
    private int|User $user;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(?int $id): Post
    {
        $this->id = $id;
        return $this;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(?string $content): Post
    {
        $this->content = $content;
        return $this;
    }

    public function getUser(): int|User
    {
        return $this->user;
    }

    public function setUser(int|User $user): Post
    {
        $this->user = $user;
        return $this;
    }
}