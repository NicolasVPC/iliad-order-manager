<?php

namespace App\Entity;

use App\Repository\ProductRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ProductRepository::class)]
class Product
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column]
    private ?int $price = null;

    #[ORM\Column(nullable: true)]
    private ?int $stock = null;

    /**
     * @var Collection<int, Order>
     */
    #[ORM\ManyToMany(targetEntity: Order::class, mappedBy: 'id_product')]
    private Collection $id_order;

    public function __construct()
    {
        $this->id_order = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(int $id): static
    {
        $this->id = $id;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getPrice(): ?int
    {
        return $this->price;
    }

    public function setPrice(int $price): static
    {
        $this->price = $price;

        return $this;
    }

    public function getStock(): ?int
    {
        return $this->stock;
    }

    public function setStock(?int $stock): static
    {
        $this->stock = $stock;

        return $this;
    }

    /**
     * @return Collection<int, Order>
     */
    public function getIdOrder(): Collection
    {
        return $this->id_order;
    }

    public function addIdOrder(Order $idOrder): static
    {
        if (!$this->id_order->contains($idOrder)) {
            $this->id_order->add($idOrder);
            $idOrder->addIdProduct($this);
        }

        return $this;
    }

    public function removeIdOrder(Order $idOrder): static
    {
        if ($this->id_order->removeElement($idOrder)) {
            $idOrder->removeIdProduct($this);
        }

        return $this;
    }
}
