<?php

namespace App\Models;

class Order
{
    private int $id;
    private string $sessionId;
    private string $customerName;
    private string $customerEmail;
    private ?string $customerPhone;
    private string $shippingAddress;
    private int|float $totalAmount;
    private string $status;
    private string $createdAt;
    private array $items = [];

    public function __construct(
        int $id,
        string $sessionId,
        string $customerName,
        string $customerEmail,
        string $shippingAddress,
        int|float $totalAmount,
        string $status = 'pending',
        ?string $customerPhone = null,
        string $createdAt = ''
    ) {
        $this->id = $id;
        $this->sessionId = $sessionId;
        $this->customerName = $customerName;
        $this->customerEmail = $customerEmail;
        $this->customerPhone = $customerPhone;
        $this->shippingAddress = $shippingAddress;
        $this->totalAmount = $totalAmount;
        $this->status = $status;
        $this->createdAt = $createdAt;
    }

    public function getId(): int { return $this->id; }
    public function setId(int $id): void { $this->id = $id; }

    public function getSessionId(): string { return $this->sessionId; }
    public function setSessionId(string $sessionId): void { $this->sessionId = $sessionId; }

    public function getCustomerName(): string { return $this->customerName; }
    public function setCustomerName(string $name): void { $this->customerName = $name; }

    public function getCustomerEmail(): string { return $this->customerEmail; }
    public function setCustomerEmail(string $email): void { $this->customerEmail = $email; }

    public function getCustomerPhone(): ?string { return $this->customerPhone; }
    public function setCustomerPhone(?string $phone): void { $this->customerPhone = $phone; }

    public function getShippingAddress(): string { return $this->shippingAddress; }
    public function setShippingAddress(string $address): void { $this->shippingAddress = $address; }

    public function getTotalAmount(): int|float { return $this->totalAmount; }
    public function setTotalAmount(int|float $amount): void { $this->totalAmount = $amount; }

    public function getStatus(): string { return $this->status; }
    public function setStatus(string $status): void { $this->status = $status; }

    public function getCreatedAt(): string { return $this->createdAt; }
    public function setCreatedAt(string $createdAt): void { $this->createdAt = $createdAt; }

    public function getItems(): array { return $this->items; }
    public function setItems(array $items): void { $this->items = $items; }
}

