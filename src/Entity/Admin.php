<?php

namespace App\Entity;

use App\Repository\AdminRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=AdminRepository::class)
 */
class Admin
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255,unique=true)
     */
    private $email;

    /**
     * @ORM\Column(type="text")
     */
    private $token;

    /**
     * @ORM\Column(type="text")
     */
    private $password;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $is_hashed;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    public function getToken(): ?string
    {
        return $this->token;
    }

    public function setToken(string $token): self
    {
        $this->token = $token;

        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    public function getIsHashed(): ?bool
    {
        return $this->is_hashed;
    }

    public function setIsHashed(?bool $is_hashed): self
    {
        $this->is_hashed = $is_hashed;

        return $this;
    }
}
