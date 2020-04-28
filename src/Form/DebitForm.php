<?php

declare(strict_types=1);

namespace App\Form;

use Symfony\Component\Validator\Constraints as Assert;

class DebitForm
{
    /**
     * @Assert\NotBlank()
     * @Assert\Positive()
     *
     * @var string|null
     */
    private $amount;

    public function getAmount(): ?string
    {
        return $this->amount;
    }

    public function setAmount(?string $amount): self
    {
        $this->amount = $amount;

        return $this;
    }
}
