<?php

declare(strict_types=1);

namespace App\Entity;

use DateTime;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;

/**
 * Class Transaction.
 *
 * @ORM\Entity()
 * @ORM\Table(name="transaction")
 */
class Transaction
{
    /**
     * @ORM\GeneratedValue()
     * @ORM\Id()
     * @ORM\Column(name="id", type="integer")
     *
     * @Serializer\Type("int")
     * @Serializer\SerializedName("id")
     *
     * @var int
     */
    private $id;

    /**
     * @ORM\Column(name="amount", type="decimal", precision=15, scale=2)
     *
     * @Serializer\Type("string")
     * @Serializer\SerializedName("amount")
     *
     * @var string
     */
    private $amount;

    /**
     * @ORM\Column(name="created_at", type="datetime")
     *
     * @Serializer\Type("DateTime")
     * @Serializer\SerializedName("createdAt")
     *
     * @var DateTime
     */
    private $createdAt;

    public function getId(): int
    {
        return $this->id;
    }

    public function getAmount(): string
    {
        return $this->amount;
    }

    public function setAmount(string $amount): self
    {
        $this->amount = $amount;

        return $this;
    }

    public function getCreatedAt(): DateTime
    {
        return $this->createdAt;
    }

    public function setCreatedAt(DateTime $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }
}
