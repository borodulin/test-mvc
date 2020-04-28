<?php

declare(strict_types=1);

namespace App\Form;

use Symfony\Component\Validator\Constraints as Assert;

class LoginForm
{
    /**
     * @Assert\NotBlank()
     * @Assert\Type(type="string")
     *
     * @var string|null
     */
    private $username;

    /**
     * @Assert\NotBlank()
     * @Assert\Type(type="string")
     *
     * @var string|null
     */
    private $password;

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function setUsername(?string $username): self
    {
        $this->username = $username;

        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(?string $password): self
    {
        $this->password = $password;

        return $this;
    }
}
